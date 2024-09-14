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
use Pina\Data\DataCollection;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Data\Field;
use Pina\Data\Schema;
use Pina\Export\DefaultExport;
use Pina\Controls\SortableTableView;
use Pina\NotFoundException;
use Pina\Processors\CollectionItemLinkProcessor;
use Pina\Response;

use Pina\Types\DirectoryType;

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
    protected $sortable = false;

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

    protected function getTabSchema(): Schema
    {
        return new Schema();
    }

    /**
     * @return Nav
     * @throws Exception
     */
    protected function makeTabs(): Nav
    {
        /** @var Nav $nav */
        $nav = $this->makeTabNav();
        $nav->setLocation($this->location->link('@', $this->query()->all()));

        $schema = $this->getTabSchema();
        if ($schema->isEmpty()) {
            return $nav;
        }

        $data = array_filter($this->getFilterRecord()->getSchema()->mine($this->query()->all()));//нужны данные из формы с фильтрами до нормализации
        foreach ($schema as $field) {
            /** @var Field $field */
            $type = $field->getType();
            if (!is_subclass_of($type, DirectoryType::class)) {
                throw new Exception(sprintf("Для навигации поддерживаются только наследники DirectoryType, класс %s не подходит", $type));
            }

            $variants = App::type($type)->getVariants();
            foreach ($variants as $variant) {
                $menuItem = $nav->appendLink($variant['title'], $this->location->link('@', array_merge($data, [$field->getName() => $variant['id']])));
                if (!empty($variant['badges']) && is_array($variant['badges'])) {
                    foreach ($variant['badges'] as $badge) {
                        $menuItem->append($this->makeBadge($badge));
                    }
                }
            }
        }

        return $nav;
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
        if ($this->sortable) {
            return $this->makeSortableCollectionView($data);
        }
        return $this->makeTableView($data);
    }

    protected function makeSortableCollectionView(DataTable $data)
    {
        /** @var SortableTableView $view */
        $view = App::make(SortableTableView::class);
        $view->load($data);
        return $view->setHandler(
            $this->base->resource('@/all/sortable'),
            'put',
            []
        );
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
        $schema = $this->collection->getFilterSchema($this->context()->all())->setNullable()->setMandatory(false);
        $tabSchema = $this->getTabSchema();
        foreach ($tabSchema as $tabField) {
            $schema->forgetField($tabField->getName());
            $schema->addField($tabField)->setHidden()->setNullable()->setMandatory(false);
        }
        $normalized = $schema->normalize($this->query()->all());
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