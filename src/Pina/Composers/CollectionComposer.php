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
        $this->collection = __('Список');
        $this->creation = __('Новый элемент');
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

    public function index(Location $location)
    {
        Request::setPlace('page_header', $this->collection);
        Request::setPlace('breadcrumb', $this->getBreadcrumb($location));
    }

    public function show(Location $location, DataRecord $record)
    {
        $title = $this->getItemTitle($record);
        Request::setPlace('page_header', $title);
        Request::setPlace('breadcrumb', $this->getBreadcrumb($location, $title));
    }

    public function create(Location $location)
    {
        Request::setPlace('page_header', $this->creation);
        Request::setPlace('breadcrumb', $this->getBreadcrumb($location, $this->creation));
    }

    public function section(Location $location, DataRecord $record, string $section)
    {
        $title = $this->getItemTitle($record);
        Request::setPlace('page_header', $section);
        Request::setPlace('breadcrumb', $this->getBreadcrumb($location, $title, $section));
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

    protected function getBreadcrumb(Location $location, $title = null, $section = null)
    {
        $path = [];

        $parts = array_filter([$section, $title, $this->collection]);
        $l = '@';
        foreach ($parts as $item) {
            array_unshift($path, [
                'title' => $item,
                'link' => $l == '@' ? null : $location->link($l),
                'is_active' => $l == '@' ? true : false
            ]);
            $l .= '@';
        }
        array_unshift($path, ['title' => '<i class="mdi mdi-home"></i>', 'link' => $location->link('/')]);

        $view = App::make(BreadcrumbView::class);
        $view->load(new DataTable($path, new Schema()));
        return $view;
    }


}