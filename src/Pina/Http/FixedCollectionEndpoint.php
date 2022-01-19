<?php

namespace Pina\Http;

use Pina\App;
use Pina\Arr;
use Pina\Controls\BreadcrumbView;
use Pina\Controls\ButtonRow;
use Pina\Controls\Control;
use Pina\Controls\FilterForm;
use Pina\Controls\LinkedButton;
use Pina\Controls\PagingControl;
use Pina\Controls\SidebarWrapper;
use Pina\Controls\TableView;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Data\Schema;
use Pina\Export\DefaultExport;
use Pina\NotFoundException;
use Pina\Paging;
use Pina\Request;
use Pina\TableDataGateway;

use function Pina\__;

abstract class FixedCollectionEndpoint extends Endpoint
{

    protected $exportAllowed = false;

    /** @return TableDataGateway */
    abstract function makeQuery();

    /** @return string */
    abstract function getCollectionTitle();

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

        $query = $this->makeIndexQuery($filters);

        Request::setPlace('page_header', $this->getCollectionTitle());
        Request::setPlace('breadcrumb', $this->getBreadcrumb($this->getCollectionTitle())->drawWithWrappers());

        $paging = $this->applyPaging($query, $filters);
        return $this->makeCollectionView(new DataTable($query->get(), $this->getListSchema()))
            ->after($paging)
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

        if (!$this->exportAllowed) {
            throw new NotFoundException();
        }

        /** @var DefaultExport $export */
        $export = App::load(DefaultExport::class);
        $export->setFilename($this->getCollectionTitle());
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
        if ($this->exportAllowed) {
            $buttons->append($this->makeExportButton());
        }
        return $buttons;
    }

    protected function makeExportButton()
    {
        /** @var DefaultExport $export */
        $export = App::load(DefaultExport::class);
        /** @var LinkedButton $buttons */
        $btn = App::make(LinkedButton::class);
        $btn->setLink($this->base->link('@.' . $export->getExtension(), $this->query()->all()));
        $btn->setTitle(__('Скачать'));

        return $btn;
    }

    protected function getBreadcrumb($baseTitle = '', $title = null)
    {
        $path = [];
        $path[] = ['title' => '<i class="mdi mdi-home"></i>', 'link' => $this->base->link('/')];
        $path[] = ['title' => $baseTitle, 'link' => $this->base->link('@')];
        if ($title) {
            $path[] = ['title' => $title, 'is_active' => true];
        }
        $view = App::make(BreadcrumbView::class);
        $view->load(new DataTable($path, new Schema()));
        return $view;
    }

    /**
     * @param TableDataGateway $query
     * @param array $filters
     * @return PagingControl
     */
    protected function applyPaging($query, $filters)
    {
        $paging = new Paging($this->request()->get('page'), $this->request()->get("paging", 25));
        $query->paging($paging);

        $pagingControl = new PagingControl();
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