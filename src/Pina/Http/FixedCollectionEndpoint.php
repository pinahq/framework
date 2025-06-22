<?php

namespace Pina\Http;

use Pina\App;
use Pina\Arr;
use Pina\Controls\ButtonRow;
use Pina\Controls\Control;
use Pina\Controls\FilterForm;
use Pina\Controls\PagingControl;
use Pina\Controls\TableView;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Data\Schema;
use Pina\Export\DefaultExport;
use Pina\NotFoundException;
use Pina\Paging;
use Pina\TableDataGateway;
use Pina\Composers\CollectionComposer;

use function Pina\__;

abstract class FixedCollectionEndpoint extends RichEndpoint
{

    /** @var CollectionComposer  */
    protected $composer;
    protected $exportAllowed = false;

    /** @return TableDataGateway */
    abstract function makeQuery();

    abstract protected function getCollectionTitle(): string;

    /**
     * Возвращает именование коллекции или элемента
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function title($id = '')
    {
        return $this->getCollectionTitle();
    }

    protected function makeConfiguredCollectionComposer(): CollectionComposer
    {
        return $this->makeCollectionComposer($this->getCollectionTitle(), __('Добавить'));
    }

    public function getListSchema()
    {
        return $this->makeQuery()->getSchema()->forgetField('id');
    }

    protected function getExportSchema()
    {
        return $this->getListSchema();
    }

    /** @return Schema */
    public function getFilterSchema()
    {
        //по умолчанию фильтры не доступны
        return new Schema();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function index()
    {
        $filters = Arr::only($this->query()->all(), $this->getFilterSchema()->getFieldKeys());

        $this->exportIfNeeded($filters);

        $data = $this->getDataTable($filters);

        $this->makeConfiguredCollectionComposer()->index($this->location());

        return $this->makeCollectionView($data)
            ->after($this->makePagingControl($data->getPaging(), $filters))
            ->after($this->makeIndexButtons())
            ->wrap($this->makeSidebarWrapper()->setSidebar($this->makeFilterForm()));
    }

    /**
     * @param array $filters
     * @return DataTable
     * @throws \Exception
     */
    protected function getDataTable($filters): DataTable
    {
        $query = $this->makeIndexQuery($filters);
        $paging = new Paging($this->request()->get('page'), $this->request()->get("paging", 25));
        $query->paging($paging);
        return new DataTable($query->get(), $this->getListSchema(), $paging);
    }

    /**
     * @param array $filters
     * @throws \Exception
     */
    protected function exportIfNeeded($filters)
    {
        $extension = pathinfo($this->location()->link('@'), PATHINFO_EXTENSION);
        if (empty($extension)) {
            return;
        }

        if (!$this->exportAllowed) {
            throw new NotFoundException();
        }

        /** @var DefaultExport $export */
        $export = App::load(DefaultExport::class);
        $export->setFilename('export');
        $export->load(new DataTable($this->makeExportQuery($filters)->get(), $this->getExportSchema()));
        $export->download();
        exit;
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
        $schema = $this->getFilterSchema();
        $normalized = $schema->normalize($this->query()->all());
        $form->load(new DataRecord($normalized, $schema));
        return $form;
    }

    protected function makeIndexButtons()
    {
        /** @var ButtonRow $buttons */
        $buttons = App::make(ButtonRow::class);
        if ($this->exportAllowed) {
            $buttons->append($this->makeExportButton());
        }
        return $buttons;
    }

    protected function makeExportButton()
    {
        /** @var DefaultExport $export */
        $export = App::load(DefaultExport::class);
        return $this->makeLinkedButton(__('Скачать'), $this->base()->link('@.' . $export->getExtension(), $this->query()->all()));
    }

    /**
     * @deprecated в пользу инкапсуляции paging в DataTable и отдельного метода на создание контрола makePagingControl
     * @param TableDataGateway $query
     * @param array $filters
     * @return PagingControl
     */
    protected function applyPaging($query, $filters)
    {
        $paging = new Paging($this->request()->get('page'), $this->request()->get("paging", 25));
        $query->paging($paging);
        return $this->makePagingControl($paging, $filters);
    }

    protected function makePagingControl($paging, $filters)
    {
        $pagingControl = App::make(PagingControl::class);
        $pagingControl->init($paging);
        $pagingControl->setLinkContext($filters);

        return $pagingControl;
    }

    /**
     * @param array $filters
     * @return TableDataGateway
     * @throws \Exception
     */
    protected function makeIndexQuery($filters)
    {
        return $this->addIndexQueryColumns($this->makeFilteredQuery($filters));
    }

    /**
     * @param array $filters
     * @return TableDataGateway
     * @throws \Exception
     */
    protected function makeExportQuery($filters)
    {
        return $this->addExportQueryColumns($this->makeFilteredQuery($filters));
    }

    /**
     * @param TableDataGateway $query
     * @return TableDataGateway
     */
    protected function addIndexQueryColumns($query)
    {
        return $this->addDefaultQueryColumns($query);
    }

    /**
     * @param TableDataGateway $query
     * @return TableDataGateway
     */
    protected function addExportQueryColumns($query)
    {
        return $this->addIndexQueryColumns($query);
    }

    /**
     * @param TableDataGateway $query
     * @return TableDataGateway
     */
    protected function addDefaultQueryColumns($query)
    {
        return $query;
    }


    /**
     * @param array $filters
     * @return TableDataGateway
     * @throws \Exception
     */
    protected function makeFilteredQuery($filters)
    {
        $schema = $this->getFilterSchema();
        return $this->makeQuery()->whereFilters($filters, $schema);
    }


}