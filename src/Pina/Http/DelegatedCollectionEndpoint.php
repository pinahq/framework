<?php

namespace Pina\Http;

use Exception;
use Pina\Access;
use Pina\App;
use Pina\Arr;
use Pina\Composers\CollectionComposer;
use Pina\Controls\ButtonRow;
use Pina\Controls\Control;
use Pina\Controls\FilterForm;
use Pina\Controls\Nav\Nav;
use Pina\Controls\PagingControl;
use Pina\Controls\RawHtml;
use Pina\Controls\Wrapper;
use Pina\Data\DataCollection;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Data\Field;
use Pina\Data\Schema;
use Pina\Export\DefaultExport;
use Pina\Layouts\EmptyLayout;
use Pina\NotFoundException;
use Pina\Processors\CollectionItemLinkProcessor;
use Pina\Response;

use Pina\Types\DirectoryType;

use Pina\Types\Relation;

use function Pina\__;

/**
 * Основной эндпоинт на замену CollectionEndpoint и FixedCollectionEndpoint
 * Предоставляет HTTP-интерфейс для управления коллекцией, которая пробрасывается
 * через поле $collection
 */
class DelegatedCollectionEndpoint extends RichEndpoint
{
    /** @var CollectionComposer  */
    protected $composer;

    /** @var DataCollection */
    protected $collection;

    /** @var DataCollection */
    protected $export;

    /** @var bool */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        /** @var CollectionComposer composer */
        $this->composer = App::make(CollectionComposer::class);
        $this->composer->configure(__('Перечень'), __('Создать'));
    }

    /**
     * Возвращает именование коллекции или элемента
     * @param $id
     * @return string
     * @throws Exception
     */
    public function title($id)
    {
        if ($id) {
            return $this->composer->getItemTitle($this->collection->getRecord($id, $this->context()->all()));
        }
        return $this->composer->getCollection();
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function index()
    {
        $filters = $this->getFilterRecord();
        $contextAndFilters = array_merge($filters->getData(), $this->context()->all());

        $this->exportIfNeeded($contextAndFilters);

        $data = $this->collection->getList($filters->getData(), $this->request()->get('page', 0), $this->request()->get("paging", 25), $this->context()->all());

        $data->getSchema()->pushHtmlProcessor(new CollectionItemLinkProcessor($data->getSchema(), $this->location, [], $this->context()->all()));

        $this->composer->index($this->location);

        $sidebarWrapper = $this->makeSidebarWrapper();
        $sidebarWrapper->addToSidebar($this->makeFilterForm());
        if (!$this->collection->getCreationSchema()->isEmpty()) {
            $sidebarWrapper->addToSidebar($this->makeCreateButton());
        }

        return $this->makeCollectionView($data)
            ->after($this->makePagingControl($data->getPaging()))
            ->after($this->makeIndexButtons())
            ->before($this->makeTabs())
            ->wrap($sidebarWrapper);
    }

    public function indexContextMenu($id)
    {
        /** @var Nav $menu */
        $menu = App::make(Nav::class);
        $menu->appendLink(__('Открыть в новой вкладке'), $this->base->link('@/:id', ['id' => $id]), true);

        $data = $this->collection->getRecord($id)->getData();

        $schema = $this->collection->getVariantAvailableSchema();
        foreach ($schema as $field) {
            $title = $field->getTitle();
            $name = $field->getName();
            $variants = App::type($field->getType())->setContext($data)->getVariants();

            /** @var Nav $dropdown */
            $dropdown = App::make(Nav::class);

            if ($field->getType() instanceof Relation) {
                foreach ($variants as $variant) {
                    if (in_array($variant['id'], $data[$name])) {
                        $item = $dropdown->appendAction($variant['title'], $this->base->resource('@/:id/relation', ['id' => $id]), 'delete', [$name => $variant['id']]);
                        $item->addClass('active');
                    }
                }

                foreach ($variants as $variant) {
                    if (!in_array($variant['id'], $data[$name])) {
                        $dropdown->appendAction($variant['title'], $this->base->resource('@/:id/relation', ['id' => $id]), 'post', [$name => $variant['id']]);
                    }
                }


            } else {
                foreach ($variants as $variant) {
                    $item = $dropdown->appendAction($variant['title'], $this->base->resource('@/:id/field', ['id' => $id]), 'put', [$name => $variant['id']]);
                    if ($data[$name] == $variant['id']) {
                        $item->addClass('active');
                    }
                }
            }
            $menu->appendDropdown($title, $dropdown);
        }

        return $menu->setLayout(App::load(EmptyLayout::class));
    }

    protected function getTabSchema(): Schema
    {
        return new Schema();
    }

    /**
     * @return Nav
     * @throws Exception
     */
    protected function makeTabs(): Control
    {
        $container = $this->makeTabContainer();

        $schema = $this->getTabSchema();
        if ($schema->isEmpty()) {
            return $container;
        }

        $data = array_filter($this->getFilterRecord()->getSchema()->mine($this->query()->all()));//нужны данные из формы с фильтрами до нормализации
        foreach ($schema as $field) {
            /** @var Field $field */
            $type = $field->getType();
            if (!is_subclass_of($type, DirectoryType::class)) {
                throw new Exception(sprintf("Для навигации поддерживаются только наследники DirectoryType, класс %s не подходит", $type));
            }

            /** @var Nav $nav */
            $nav = $this->makeTabNav();
            $nav->setLocation($this->location->link('@', $this->query()->all()));

            $variants = App::type($type)->getVariants();
            foreach ($variants as $variant) {
                $menuItem = $nav->appendLink($variant['title'], $this->location->link('@', array_merge($data, [$field->getName() => $variant['id']])));
                if (!empty($variant['badges']) && is_array($variant['badges'])) {
                    foreach ($variant['badges'] as $badge) {
                        $menuItem->append($this->makeBadge($badge));
                    }
                }
            }
            $container->append($nav);
        }

        return $container;
    }

    protected function makeTabContainer()
    {
        return new Wrapper('nav.tabs');
    }

    protected function makeTabNav(): Nav
    {
        $menu = App::make(Nav::class);
        $menu->addClass('nav-tabs');
        return $menu;
    }


    /**
     * @param array $filters
     * @throws Exception
     */
    protected function exportIfNeeded($filters)
    {
        $extension = pathinfo($this->location->link('@'), PATHINFO_EXTENSION);
        if (empty($extension)) {
            return;
        }

        if (!$this->export) {
            throw new NotFoundException();
        }

        /** @var DefaultExport $export */
        $export = App::load(DefaultExport::class);
        $export->setFilename('export');
        $export->load($this->export->getList($filters));
        $export->download();
        exit;
    }

    /**
     * @throws Exception
     */
    public function show($id)
    {
        $context = $this->context()->all();

        $record = $this->collection->getRecord($id, $context);

        $this->composer->show($this->location, $record);

        return $this->resolveRecordView($record)->wrap($this->makeSidebarWrapper());
    }

    /**
     * @throws Exception
     */
    public function create()
    {
        $record = $this->collection->getNewRecord($this->query()->all(), $this->context()->all());

        $this->composer->create($this->location);

        return $this->makeCreateForm($record)->wrap($this->makeSidebarWrapper());
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function store()
    {
        $data = $this->request()->all();

        $context = $this->context()->all();

        $id = $this->collection->add($data, $context);

        return Response::ok()->contentLocation($this->base->link('@/:id', ['id' => $id]));
    }

    /**
     * @param string $id
     * @return Response
     * @throws Exception
     */
    public function update($id)
    {
        $data = $this->request()->all();

        $context = $this->context()->all();

        $id = $this->collection->update($id, $data, $context);

        return Response::ok()->contentLocation($this->base->link('@/:id', ['id' => $id]));
    }

    public function updateField($tmp, $id)
    {
        $data = $this->request()->all();
        $context = $this->context()->all();

        $this->collection->update($id, $data, $context, array_keys($data));

        return Response::ok();
    }

    public function storeRelation($tmp, $id)
    {
        $data = $this->request()->all();
        $context = $this->context()->all();

        $keys = array_keys($data);
        $field = array_shift($keys);
        $value = $data[$field] ?? null;

        $this->collection->addToRelation($id, $field, $value, $context);

        return Response::ok();
    }

    public function destroyRelation($tmp, $id)
    {
        $data = $this->request()->all();
        $context = $this->context()->all();

        $keys = array_keys($data);
        $field = array_shift($keys);
        $value = $data[$field] ?? null;

        $this->collection->deleteFromRelation($id, $field, $value, $context);

        return Response::ok();
    }


    public function updateSortable()
    {
        $ids = $this->request()->all()['id'] ?? [];
        $this->collection->reorder($ids);

        return Response::ok()->emptyContent();
    }

    /**
     * @return Control
     */
    protected function makeCollectionView(DataTable $data)
    {
        return $this->makeTableView($data)->setLocation($this->base, $this->context()->all());
    }

    /**
     * @return Control
     * @throws Exception
     */
    protected function makeFilterForm()
    {
        /** @var FilterForm $form */
        $form = App::make(FilterForm::class);
        $record = $this->getFilterRecord();
        $form->load($record);
        $form->getButtonRow()->append($this->makeResetButton());
        return $form;
    }

    /**
     * @return DataRecord
     * @throws Exception
     */
    protected function getFilterRecord(): DataRecord
    {
        $context = $this->context()->all();
        $schema = $this->collection->getFilterSchema($context)->setNullable()->setMandatory(false);
        $tabSchema = $this->getTabSchema();
        foreach ($tabSchema as $tabField) {
            $schema->forgetField($tabField->getName());
            $schema->addField($tabField)->setHidden()->setNullable()->setMandatory(false);
        }
        $normalized = array_merge($schema->normalize($this->query()->all()), $context);
        return new DataRecord($normalized, $schema);
    }

    protected function makeIndexButtons()
    {
        /** @var ButtonRow $buttons */
        $buttons = App::make(ButtonRow::class);
        if ($this->export) {
            $buttons->append($this->makeExportButton());
        }
        if (!$this->collection->getCreationSchema()->isEmpty()) {
            $buttons->setMain($this->makeCreateButton()->setStyle('primary'));
        }
        return $buttons;
    }

    protected function makeExportButton()
    {
        /** @var DefaultExport $export */
        $export = App::load(DefaultExport::class);
        $link = $this->base->link('@.' . $export->getExtension(), $this->query()->all());
        return $this->makeLinkedButton(__('Скачать'), $link);
    }

    protected function makePagingControl($paging)
    {
        //значимые фильтры, если расширить до всех параметров, то все будут попадать в пагинацию
        $filters = Arr::only($this->query()->all(), $this->collection->getFilterSchema($this->context()->all())->getFieldKeys());

        /** @var PagingControl $pagingControl */
        $pagingControl = App::make(PagingControl::class);
        $pagingControl->init($paging);
        $pagingControl->setLinkContext($filters);
        return $pagingControl;
    }

    /**
     * @return Control
     */
    protected function resolveRecordView(DataRecord $data)
    {
        $display = $this->query()->get('display');
        $component = $display == 'edit' ? $this->makeEditForm($data) : $this->makeViewForm($data);
        return $component;
    }

    /**
     * @return Control
     */
    protected function makeEditForm(DataRecord $data)
    {
        $form = $this->makeRecordForm($this->location->link('@'), 'put', $data);
        $form->getButtonRow()->append($this->makeCancelButton());
        return $form;
    }

    /**
     * @return Control
     */
    protected function makeViewForm(DataRecord $record)
    {
        return $this->makeRecordView($record)->after($this->makeViewButtonRow($record));
    }

    /**
     * @return ButtonRow
     */
    protected function makeViewButtonRow(DataRecord $record)
    {
        /** @var ButtonRow $row */
        $row = App::make(ButtonRow::class);
        $row->addClass('mb-5');
        if ($this->collection->getSchema()->isEditable()) {
            $row->setMain($this->makeEditLinkButton());
        }
        $this->appendNestedResourceButtons($row);
        return $row;
    }

    protected function makePreviousButton(DataRecord $record): Control
    {
        $context = $this->context()->all();
        $prevId = $this->collection->getPreviousId($record->getSinglePrimaryKey($context), $context);
        if (empty($prevId)) {
            return new RawHtml();
        }
        return $this->makeLinkedButton('⟵', $this->base->link('@/:id', ['id' => $prevId]), 'info');
    }

    protected function makeNextButton(DataRecord $record): Control
    {
        $context = $this->context()->all();
        $nextId = $this->collection->getNextId($record->getSinglePrimaryKey($context), $context);
        if (empty($nextId)) {
            return new RawHtml();
        }
        return $this->makeLinkedButton('⟶', $this->base->link('@/:id', ['id' => $nextId]), 'info');
    }

    protected function appendNestedResourceButtons(Control $control)
    {
        $childs = App::router()->findChilds($this->location->resource('@'));
        foreach ($childs as $resource) {
            if (!Access::isPermitted($resource)) {
                continue;
            }
            try {
                $title = App::router()->run($resource, 'title');
                if ($title) {
                    $control->append($this->makeLinkedButton($title, $this->location->link($resource)));
                }
            } catch (Exception $e) {

            }
        }
        return $control;
    }

    /**
     * @return Control
     */
    protected function makeCreateForm(DataRecord $data)
    {
        return $this->makeRecordForm($this->base->link('@'), 'post', $data);
    }

    protected function makeCancelButton()
    {
        return $this->makeLinkedButton(__('Отменить'), $this->location->link('@'));
    }

    protected function makeCreateButton()
    {
        return $this->makeLinkedButton(__('Добавить'), $this->base->link('@/create'));
    }

    protected function makeResetButton()
    {
        return $this->makeLinkedButton(__('Сбросить'), $this->base->link('@'));
    }

    protected function makeEditLinkButton()
    {
        return $this->makeLinkedButton(__('Редактировать'), $this->location->link('@', ['display' => 'edit']), 'primary');
    }

}