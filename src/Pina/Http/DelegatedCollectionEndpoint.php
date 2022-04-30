<?php

namespace Pina\Http;

use Pina\App;
use Pina\Arr;
use Pina\Composers\CollectionComposer;
use Pina\Controls\ButtonRow;
use Pina\Controls\Control;
use Pina\Controls\FilterForm;
use Pina\Controls\LinkedButton;
use Pina\Controls\PagingControl;
use Pina\Controls\RecordForm;
use Pina\Controls\RecordView;
use Pina\Controls\SidebarWrapper;
use Pina\Controls\TableView;
use Pina\Data\DataCollection;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Data\Schema;
use Pina\Export\DefaultExport;
use Pina\NotFoundException;
use Pina\Paging;
use Pina\Processors\CollectionItemLinkProcessor;
use Pina\Response;
use Pina\TableDataGateway;

use function Pina\__;

/**
 * Основной эндпоинт на замену CollectionEndpoint и FixedCollectionEndpoint
 * Предоставляет HTTP-интерфейс для управления коллекцией, которая пробрасывается
 * через поле $collection
 */
class DelegatedCollectionEndpoint extends Endpoint
{
    protected $composer;

    /** @var DataCollection */
    protected $collection;

    /** @var DataCollection */
    protected $export;

    public function __construct(Request $request, Location $location, Location $base)
    {
        parent::__construct($request, $location, $base);
        /** @var CollectionComposer composer */
        $this->composer = App::make(CollectionComposer::class);
        $this->composer->configure(__('Перечень'), '', __('Создать'));
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function index()
    {
        //значимые фильтры, если расширить до всех параметров, то все будут попадать в пагинацию
        $filters = Arr::only($this->query()->all(), $this->collection->getFilterSchema()->getFieldKeys());

        $this->exportIfNeeded($filters);

        $data = $this->collection->getList($this->query()->all(), $this->request()->get('page'), $this->request()->get("paging", 25));

        $data->getSchema()->pushHtmlProcessor(new CollectionItemLinkProcessor($data->getSchema(), $this->location));

        $this->composer->index($this->base, $data);

        return $this->makeCollectionView($data)
            ->after($this->makePagingControl($data->getPaging(), $filters))
            ->after($this->makeIndexButtons())
            ->wrap($this->makeSidebarWrapper()->setSidebar($this->makeFilterForm()));
    }


    /**
     * @param array $filters
     * @throws \Exception
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

    public function show($id)
    {
        $record = $this->collection->getRecord($id);

        $this->composer->show($this->base, $record);

        return $this->makeRecordView($record)->wrap($this->makeSidebarWrapper());
    }

    public function create()
    {
        $record = $this->collection->getNewRecord($this->query()->all());

        $this->composer->create($this->base, $record);

        return $this->makeCreateForm($record)->wrap($this->makeSidebarWrapper());
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function store()
    {
        $data = $this->request()->all();

        $id = $this->collection->add($data);

        return Response::ok()->contentLocation($this->base->link('@/:id', ['id' => $id]));
    }

    /**
     * @param string $id
     * @return Response
     * @throws \Exception
     */
    public function update($id)
    {
        $data = $this->request()->all();

        $id = $this->collection->update($id, $data);

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
        return App::make(TableView::class)->load($data);
    }

    /**
     * @return Control
     * @throws \Exception
     */
    protected function makeFilterForm()
    {
        /** @var FilterForm $form */
        $form = App::make(FilterForm::class);
        $schema = $this->collection->getFilterSchema();
        $normalized = $schema->normalize($this->query()->all());
        $form->load(new DataRecord($normalized, $schema));
        if (!$this->collection->getCreationSchema()->isEmpty()) {
            $form->getButtonRow()->append($this->makeCreateButton());
        }
        return $form;
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

    protected function makePagingControl($paging, $filters)
    {
        $pagingControl = new PagingControl();
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
        $row->setMain($this->makeEditLinkButton());
        return $row;
    }

    /**
     * @return Control
     */
    protected function makeCreateForm(DataRecord $data)
    {
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