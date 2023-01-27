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
use Pina\Controls\LinkedButton;
use Pina\Controls\Nav;
use Pina\Controls\PagingControl;
use Pina\Controls\RecordForm;
use Pina\Controls\RecordView;
use Pina\Controls\SidebarWrapper;
use Pina\Controls\TableView;
use Pina\Data\DataCollection;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Data\Field;
use Pina\Data\Schema;
use Pina\Export\DefaultExport;
use Pina\Controls\SortableTableView;
use Pina\Model\LinkedItem;
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
class DelegatedCollectionEndpoint extends Endpoint
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
            return $this->composer->getItemTitle($this->collection->getRecord($id));
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

        $data = $this->collection->getList($contextAndFilters, $this->request()->get('page', 0), $this->request()->get("paging", 25));

        $data->getSchema()->pushHtmlProcessor(new CollectionItemLinkProcessor($data->getSchema(), $this->location, [], $this->context()->all()));

        $this->composer->index($this->location);

        return $this->makeCollectionView($data)
            ->after($this->makePagingControl($data->getPaging()))
            ->after($this->makeIndexButtons())
            ->before($this->makeTabs())
            ->wrap($this->makeSidebarWrapper()->setSidebar($this->makeFilterForm()));
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
        /** @var Nav $menu */
        $menu = App::make(Nav::class);
        $menu->addClass('nav nav-tabs');
        $menu->setLocation($this->location->link('@', $this->query()->all()));

        $data = $this->getFilterRecord()->getData();

        $schema = $this->getTabSchema();
        foreach ($schema as $field) {
            /** @var Field $field */
            $type = $field->getType();
            if (!is_subclass_of($type, DirectoryType::class)) {
                throw new Exception(sprintf("Для навигации поддерживаются только наследники DirectoryType, класс %s не подходит", $type));
            }

            $variants = App::type($type)->getVariants();
            foreach ($variants as $variant) {
                $menu->addItem(new LinkedItem($variant['title'], $this->location->link('@', array_merge($data, [$field->getKey() => $variant['id']]))));
            }
            break;
        }

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

        return $this->makeRecordView($record)->wrap($this->makeSidebarWrapper());
    }

    /**
     * @throws Exception
     */
    public function create()
    {
        $record = $this->collection->getNewRecord($this->query()->all());

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
        $ids = $this->request()->get('id');

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
        return App::make(TableView::class)->load($data);
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
        foreach ($this->context()->all() as $key => $value) {
            $record->getSchema()->forgetField($key);
        }
        $form->load($record);
        if (!$this->collection->getCreationSchema()->isEmpty()) {
            $form->getButtonRow()->append($this->makeCreateButton());
        }
        return $form;
    }

    /**
     * @return DataRecord
     * @throws Exception
     */
    protected function getFilterRecord(): DataRecord
    {
        $schema = $this->collection->getFilterSchema()->setNullable()->setMandatory(false);
        $tabSchema = $this->getTabSchema();
        foreach ($tabSchema as $tabField) {
            $schema->forgetField($tabField->getKey());
            $schema->addField($tabField)->setHidden()->setNullable()->setMandatory(false);
        }
        $normalized = $schema->normalize($this->query()->all());
        return new DataRecord($normalized, $schema);
    }

    /**
     *
     * @return SidebarWrapper
     */
    protected function makeSidebarWrapper()
    {
        return App::make(SidebarWrapper::class);
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
        return $this->makeButton($link, __('Скачать'));
    }

    protected function makePagingControl($paging)
    {
        //значимые фильтры, если расширить до всех параметров, то все будут попадать в пагинацию
        $filters = Arr::only($this->query()->all(), $this->collection->getFilterSchema()->getFieldKeys());

        /** @var PagingControl $pagingControl */
        $pagingControl = App::make(PagingControl::class);
        $pagingControl->init($paging);
        $pagingControl->setLinkContext($filters);
        return $pagingControl;
    }

    /**
     * @return Control
     */
    protected function makeRecordView(DataRecord $data)
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
        /** @var RecordForm $form */
        $form = App::make(RecordForm::class);
        $form->setMethod('put')->setAction($this->location->link('@'));
        $form->getButtonRow()->append($this->makeCancelButton());
        $form->load($data);
        return $form;
    }

    /**
     * @return Control
     */
    protected function makeViewForm(DataRecord $data)
    {
        return App::make(RecordView::class)->load($data)->after($this->makeViewButtonRow());
    }

    /**
     * @return ButtonRow
     */
    protected function makeViewButtonRow()
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
                    $control->append($this->makeResourceButton($title, $resource));
                }
            } catch (Exception $e) {

            }
        }
        return $control;
    }

    protected function makeResourceButton($title, $resource)
    {
        /** @var LinkedButton $button */
        $button = App::make(LinkedButton::class);
        $button->setTitle($title);
        $button->setLink($this->location->link($resource));
        return $button;
    }

    /**
     * @return Control
     */
    protected function makeCreateForm(DataRecord $data)
    {
        foreach ($this->context()->all() as $key => $value) {
            $data->getSchema()->forgetField($key);
        }
        /** @var RecordForm $form */
        $form = App::make(RecordForm::class);
        $form->setMethod('post')->setAction($this->base->link('@'));
        $form->load($data);
        return $form;
    }

    protected function makeCancelButton()
    {
        return $this->makeButton($this->location->link('@'), __('Отменить'));
    }

    protected function makeCreateButton()
    {
        return $this->makeButton($this->base->link('@/create'), __('Добавить'));
    }

    protected function makeEditLinkButton()
    {
        return $this->makeButton($this->location->link('@', ['display' => 'edit']), __('Редактировать'), 'primary');
    }

    protected function makeButton($link, $title, $style = '')
    {
        $button = App::make(LinkedButton::class);
        $button->setLink($link);
        $button->setTitle($title);
        if ($style) {
            $button->setStyle($style);
        }
        return $button;
    }

}