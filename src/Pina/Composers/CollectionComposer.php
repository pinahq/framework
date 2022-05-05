<?php

namespace Pina\Composers;

use Pina\App;
use Pina\Controls\BreadcrumbView;
use Pina\Data\DataTable;
use Pina\Data\DataRecord;
use Pina\Data\Schema;
use Pina\Request;
use Pina\Http\Location;

use function \Pina\__;

class CollectionComposer
{
    protected $collection;
    protected $creation;
    protected $itemCallback;

    public function __construct()
    {
        $this->collection = __('Перечень');
        $this->creation = __('Создать');
    }

    public function configure(string $collection, string $creation)
    {
        $this->collection = $collection;
        $this->creation = $creation;
    }

    public function setItemCallback(Callable $callback)
    {
        $this->itemCallback = $callback;
    }

    public function index(Location $base, DataTable $data)
    {
        Request::setPlace('page_header', $this->collection);
        Request::setPlace('breadcrumb', $this->getBreadcrumb($base));
    }

    public function show(Location $base, DataRecord $record)
    {
        $title = $this->getItemTitle($record);
        Request::setPlace('page_header', $title);
        Request::setPlace('breadcrumb', $this->getBreadcrumb($base, $title));
    }

    public function create(Location $base, DataRecord $record)
    {
        Request::setPlace('page_header', $this->creation);
        Request::setPlace('breadcrumb', $this->getBreadcrumb($base, $this->creation));
    }

    protected function getItemTitle(DataRecord $record)
    {
        if ($this->itemCallback) {
            $fn = $this->itemCallback;
            return $title = $fn($record);
        }
        $title = $record->getMeta('title');
        if (empty($title)) {
            $title = $record->getMeta('id');
        }
        return trim($title);
    }

    protected function getBreadcrumb(Location $base, $title = null)
    {
        $path = [];
        $path[] = ['title' => '<i class="mdi mdi-home"></i>', 'link' => $base->link('/')];
        $path[] = ['title' => $this->collection, 'link' => $base->link('@')];
        if ($title) {
            $path[] = ['title' => $title, 'is_active' => true];
        }
        $view = App::make(BreadcrumbView::class);
        $view->load(new DataTable($path, new Schema()));
        return $view;
    }


}