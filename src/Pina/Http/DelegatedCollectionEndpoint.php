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
use Pina\Controls\SidebarWrapper;
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
 * через абстрактный метод makeDataCollection
 */
abstract class DelegatedCollectionEndpoint extends RichEndpoint
{
    abstract protected function getCollectionTitle(): string;

    abstract protected function makeDataCollection(): DataCollection;

    protected function makeExportDataCollection(): ?DataCollection
    {
        return null;
    }

    /**
     * Возвращает именование коллекции или элемента
     * @param $id
     * @return string
     * @throws Exception
     */
    public function title($id = '')
    {
        if ($id) {
            return $this->makeConfiguredCollectionComposer()->getItemTitle($this->makeDataCollection()->getRecord($id, $this->context()->all()));
        }
        return $this->getCollectionTitle();
    }

    protected function makeConfiguredCollectionComposer(): CollectionComposer
    {
        return $this->makeCollectionComposer($this->getCollectionTitle(), __('Добавить'));
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

        $collection = $this->makeDataCollection();
        $data = $collection->getList($filters->getData(), $this->request()->get('page', 0), $this->request()->get("paging", 25), $this->context()->all());

        $data->getSchema()->pushHtmlProcessor(new CollectionItemLinkProcessor($data->getSchema(), $this->location(), [], $this->context()->all()));

        $this->makeConfiguredCollectionComposer()->index($this->location());

        $sidebarWrapper = $this->makeSidebarWrapper();
        if ($data->count() || $this->query()->all()) {
            //если данных нет и нет поискового запроса, который обнулил данные,
            // то не имеет смысла показывать форму фильтрации,
            // а кнопка добавить внизу списка будет практически наверху, зачем ее дублировать
            $sidebarWrapper->addToSidebar($this->makeFilterForm());
            if (!$collection->getCreationSchema()->isEmpty()) {
                $sidebarWrapper->addToSidebar($this->makeCreateButton());
            }
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
        $menu->appendLink(__('Открыть в новой вкладке'), $this->base()->link('@/:id', ['id' => $id]), true);

        $collection = $this->makeDataCollection();
        $data = $collection->getRecord($id)->getData();

        $schema = $collection->getVariantAvailableSchema();
        foreach ($schema as $field) {
            $title = $field->getTitle();
            $name = $field->getName();
            $variants = App::type($field->getType())->setContext($data)->getVariants();

            /** @var Nav $dropdown */
            $dropdown = App::make(Nav::class);

            if ($field->getType() instanceof Relation) {
                foreach ($variants as $variant) {
                    if (in_array($variant['id'], $data[$name])) {
                        $item = $dropdown->appendAction($variant['title'], $this->base()->resource('@/:id/relation', ['id' => $id]), 'delete', [$name => $variant['id']]);
                        $item->addClass('active');
                    }
                }

                foreach ($variants as $variant) {
                    if (!in_array($variant['id'], $data[$name])) {
                        $dropdown->appendAction($variant['title'], $this->base()->resource('@/:id/relation', ['id' => $id]), 'post', [$name => $variant['id']]);
                    }
                }


            } else {
                foreach ($variants as $variant) {
                    $item = $dropdown->appendAction($variant['title'], $this->base()->resource('@/:id/field', ['id' => $id]), 'put', [$name => $variant['id']]);
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
        $schema = $this->getTabSchema();
        if ($schema->isEmpty()) {
            return new RawHtml();
        }

        $container = $this->makeTabContainer();
        foreach ($schema as $field) {
            /** @var Field $field */
            $nav = $this->makeFieldTabs($field);
            $container->append($nav);
        }

        return $container;
    }

    protected function makeFieldTabs(Field $field)
    {
        $type = $field->getType();
        if (!is_subclass_of($type, DirectoryType::class)) {
            throw new Exception(sprintf("Для навигации поддерживаются только наследники DirectoryType, класс %s не подходит", $type));
        }

        /** @var Nav $nav */
        $nav = $this->makeTabNav();
        $nav->setLocation($this->location()->link('@', $this->query()->all()));

        $data = array_filter($this->getFilterRecord()->getSchema()->mine($this->query()->all()));//нужны данные из формы с фильтрами до нормализации

        $variants = App::type($type)->getVariants();
        foreach ($variants as $variant) {
            $menuItem = $nav->appendLink($variant['title'], $this->location()->link('@', array_merge($data, [$field->getName() => $variant['id']])));
            if (!empty($variant['badges']) && is_array($variant['badges'])) {
                foreach ($variant['badges'] as $badge) {
                    $menuItem->append($this->makeBadge($badge));
                }
            }
        }

        return $nav;
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
        $extension = pathinfo($this->location()->link('@'), PATHINFO_EXTENSION);
        if (empty($extension)) {
            return;
        }

        $collection = $this->makeExportDataCollection();
        if (!$collection) {
            throw new NotFoundException();
        }

        /** @var DefaultExport $export */
        $export = App::load(DefaultExport::class);
        $export->setFilename('export');
        $export->load($collection->getList($filters));
        $export->download();
        exit;
    }

    /**
     * @throws Exception
     */
    public function show($id)
    {
        $context = $this->context()->all();

        $record = $this->makeDataCollection()->getRecord($id, $context);

        $this->makeConfiguredCollectionComposer()->show($this->location(), $record);

        $sidebarWrapper = $this->makeSidebarWrapper();
        $this->appendNestedResourceButtons($sidebarWrapper);

        return $this->resolveRecordView($record)->wrap($sidebarWrapper);
    }

    /**
     * @throws Exception
     */
    public function create()
    {
        $record = $this->makeDataCollection()->getNewRecord($this->query()->all(), $this->context()->all());

        $this->makeConfiguredCollectionComposer()->create($this->location());

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

        $id = $this->makeDataCollection()->add($data, $context);

        return Response::ok()->contentLocation($this->base()->link('@/:id', ['id' => $id]));
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

        $id = $this->makeDataCollection()->update($id, $data, $context);

        return Response::ok()->contentLocation($this->base()->link('@/:id', ['id' => $id]));
    }

    public function updateField($tmp, $id)
    {
        $data = $this->request()->all();
        $context = $this->context()->all();

        $this->makeDataCollection()->update($id, $data, $context, array_keys($data));

        return Response::ok();
    }

    public function storeRelation($tmp, $id)
    {
        $data = $this->request()->all();
        $context = $this->context()->all();

        $keys = array_keys($data);
        $field = array_shift($keys);
        $value = $data[$field] ?? null;

        $this->makeDataCollection()->addToRelation($id, $field, $value, $context);

        return Response::ok();
    }

    public function destroyRelation($tmp, $id)
    {
        $data = $this->request()->all();
        $context = $this->context()->all();

        $keys = array_keys($data);
        $field = array_shift($keys);
        $value = $data[$field] ?? null;

        $this->makeDataCollection()->deleteFromRelation($id, $field, $value, $context);

        return Response::ok();
    }


    public function updateSortable()
    {
        $ids = $this->request()->all()['id'] ?? [];
        $this->makeDataCollection()->reorder($ids);

        return Response::ok()->emptyContent();
    }

    /**
     * @return Control
     */
    protected function makeCollectionView(DataTable $data)
    {
        return $this->makeTableView($data)->setLocation($this->base(), $this->context()->all());
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
        $schema = $this->makeDataCollection()->getFilterSchema($context)->setNullable()->setMandatory(false);
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
        if ($this->makeExportDataCollection()) {
            $buttons->append($this->makeExportButton());
        }
        if (!$this->makeDataCollection()->getCreationSchema()->isEmpty()) {
            $buttons->setMain($this->makeCreateButton()->setStyle('primary'));
        }
        return $buttons;
    }

    protected function makeExportButton()
    {
        /** @var DefaultExport $export */
        $export = App::load(DefaultExport::class);
        $link = $this->base()->link('@.' . $export->getExtension(), $this->query()->all());
        return $this->makeLinkedButton(__('Скачать'), $link);
    }

    protected function makePagingControl($paging)
    {
        //значимые фильтры, если расширить до всех параметров, то все будут попадать в пагинацию
        $filters = Arr::only($this->query()->all(), $this->makeDataCollection()->getFilterSchema($this->context()->all())->getFieldKeys());

        /** @var PagingControl $pagingControl */
        $pagingControl = App::make(PagingControl::class);
        $pagingControl->init($paging);
        $pagingControl->setLinkContext($filters);
        return $pagingControl;
    }

    protected function makePreviousButton(DataRecord $record): Control
    {
        $context = $this->context()->all();
        $prevId = $this->makeDataCollection()->getPreviousId($record->getSinglePrimaryKey($context), $context);
        if (empty($prevId)) {
            return new RawHtml();
        }
        return $this->makeLinkedButton('⟵', $this->base()->link('@/:id', ['id' => $prevId]), 'info');
    }

    protected function makeNextButton(DataRecord $record): Control
    {
        $context = $this->context()->all();
        $nextId = $this->makeDataCollection()->getNextId($record->getSinglePrimaryKey($context), $context);
        if (empty($nextId)) {
            return new RawHtml();
        }
        return $this->makeLinkedButton('⟶', $this->base()->link('@/:id', ['id' => $nextId]), 'info');
    }

    protected function appendNestedResourceButtons(SidebarWrapper $control)
    {
        $childs = App::router()->findChilds($this->location()->resource('@'));
        foreach ($childs as $resource) {
            if (!Access::isPermitted($resource)) {
                continue;
            }
            try {
                $title = App::router()->run($resource, 'title');
                if ($title) {
                    $control->addToSidebar($this->makeLinkedButton($title, $this->location()->link($resource)));
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
        return $this->makeRecordForm($this->base()->link('@'), 'post', $data);
    }

    protected function makeCreateButton()
    {
        return $this->makeLinkedButton(__('Добавить'), $this->base()->link('@/create'));
    }

    protected function makeResetButton()
    {
        return $this->makeLinkedButton(__('Сбросить'), $this->base()->link('@'));
    }

}