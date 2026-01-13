<?php

namespace Pina\Composers;

use Exception;
use Pina\App;
use Pina\Controls\BreadcrumbView;
use Pina\Controls\Meta;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Data\Schema;
use Pina\Http\Location;
use Pina\Model\LinkedItem;
use Pina\Model\LinkedItemCollection;
use function Pina\__;

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

    public function getCollection()
    {
        return $this->collection;
    }

    public function setItemCallback(Callable $callback)
    {
        $this->itemCallback = $callback;
    }

    public function index(Location $location)
    {
        $links = $this->getParentLinks($location->location('@@'));
        $links->add(new LinkedItem($this->collection, $location->link('@')));

        $this->meta()->set('title', $this->collection);

        App::place('page_header')->set($this->collection);
        App::place('breadcrumb')->set($this->getBreadcrumb($links));
    }

    public function showTitle(Location $location, string $title)
    {
        $links = $this->getParentLinks($location->location('@@@'));
        $links->add(new LinkedItem($this->collection, $location->link('@@')));
        $links->add(new LinkedItem($title, $location->link('@')));

        $this->meta()->set('title', $title);

        App::place('page_header')->set($title);
        App::place('breadcrumb')->set($this->getBreadcrumb($links));
    }

    public function show(Location $location, DataRecord $record)
    {
        $this->showTitle($location, $this->getItemTitle($record));
    }

    public function create(Location $location)
    {
        $links = $this->getParentLinks($location->location('@@@'));
        $links->add(new LinkedItem($this->collection, $location->link('@@')));
        $links->add(new LinkedItem($this->creation, $location->link('@')));

        $this->meta()->set('title', $this->creation);

        App::place('page_header')->set($this->creation);
        App::place('breadcrumb')->set($this->getBreadcrumb($links));
    }

    public function section(Location $location, string $section)
    {
        $links = $this->getParentLinks($location->location('@@'));
        $links->add(new LinkedItem($section, $location->link('@')));

        $this->meta()->set('title', $section);

        App::place('page_header')->set($section);
        App::place('breadcrumb')->set($this->getBreadcrumb($links));
    }

    public function getItemTitle(DataRecord $record)
    {
        if ($this->itemCallback) {
            $fn = $this->itemCallback;
            return $title = $fn($record);
        }
        $title = $record->getMeta('title');
        if (empty($title)) {
            $parts = [];
            foreach ($record->getSchema() as $field) {
                if ($field->hasTag('title')) {
                    $parts[] = $record->getMeta($field->getName());
                }
            }
            $title = implode(' ', $parts);
        }
        if (empty($title)) {
            $title = $record->getMeta('id');
        }
        return trim($title);
    }

    protected function getParentLinks(Location $location): LinkedItemCollection
    {
        if (!$location->resource('@')) {
            $links = new LinkedItemCollection();
            try {
                $title = App::router()->run('/', 'title');
                if (is_string($title)) {
                    $links->add(new LinkedItem($title, '/'));
                }
            } catch (Exception $e) {
            }
            return $links;
        }

        $links = $this->getParentLinks($location->location('@@'));

        try {
            $title = App::router()->run($location->resource('@'), 'title');
            if ($title && is_string($title)) {
                $links->add(new LinkedItem($title, $location->link('@')));
            }
        } catch (Exception $e) {
        }
        return $links;
    }

    protected function getBreadcrumb(LinkedItemCollection $links)
    {
        $path = [];
        foreach ($links as $link) {
            $path[] = [
                'title' => $link->getTitle(),
                'link' => $link->getLink(),
                'is_active' => false
            ];
        }
//        array_unshift($path, ['title' => 'Home', 'link' => App::link('/')]);

        $path[count($path) - 1]['is_active'] = true;
        $path[count($path) - 1]['link'] = null;

        $view = App::make(BreadcrumbView::class);
        $view->load(new DataTable($path, new Schema()));
        return $view;
    }

    protected function meta(): Meta
    {
        return App::load(Meta::class);
    }

}