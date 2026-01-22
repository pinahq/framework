<?php

namespace Pina\Menu;

use Pina\App;
use Pina\Controls\Nav\Nav;
use Pina\Http\Location;
use Pina\Input;
use Pina\Url;

class RouterSiblingMenu extends Nav
{

    public function __construct()
    {
        parent::__construct();

        $current = Input::getResource();
        list($controller, $action) = Url::route($current, 'get');
        $location = new Location($current);
        $itemResource = $location->resource($action == 'show' ? '@' : '@@');

        $count = count($this->innerAfter);

        $childs = App::router()->findChilds($itemResource);
        foreach ($childs as $resource) {
            $this->appendItem($resource);
        }
        if (count($this->innerAfter) > $count) {
            $this->prependItem($itemResource);
        }
    }

    protected function appendItem($resource)
    {
        if (!App::access()->isPermitted($resource)) {
            return;
        }
        try {
            $title = App::router()->run($resource, 'title');
            if ($title && is_string($title)) {
                $this->appendLink($title, App::link($resource));
            }
        } catch (\Exception $e) {
        }
    }

    protected function prependItem($resource)
    {
        if (!App::access()->isPermitted($resource)) {
            return;
        }
        try {
            $title = App::router()->run($resource, 'title');
            if ($title && is_string($title)) {
                $this->prependLink($title, App::link($resource));
            }
        } catch (\Exception $e) {
        }
    }

}