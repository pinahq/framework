<?php

use PHPUnit\Framework\TestCase;
use Pina\Url;
use Pina\Route;
use Pina\App;

class UrlTest extends TestCase
{

    /**
     * @dataProvider trimProvider
     */
    public function testTrim($resource, $expected)
    {
        $this->assertEquals($expected, Url::trim($resource));
    }

    public function trimProvider()
    {
        return array(
            array('menus/5/items.admin', 'menus/5/items'),
            array('menus/5/items.html', 'menus/5/items'),
            array('menus/5/items', 'menus/5/items'),
            array('/menus/5/items.admin', 'menus/5/items'),
            array('/menus/5/items.html', 'menus/5/items'),
            array('/menus/5/items', 'menus/5/items'),
        );
    }

    /**
     * @dataProvider controllerProvider
     */
    public function testController($resource, $expected)
    {
        $this->assertEquals($expected, Url::controller($resource));
    }

    public function controllerProvider()
    {
        return array(
            array('menus/5/items.admin', 'menus/items'),
            array('menus/5/items.html', 'menus/items'),
            array('menus/5/items', 'menus/items'),
            array('/menus/5/items.admin', 'menus/items'),
            array('/menus/5/items.html', 'menus/items'),
            array('/menus/5/items', 'menus/items'),
            array('menus.admin', 'menus'),
            array('menus.html', 'menus'),
            array('menus', 'menus'),
            array('/menus.admin', 'menus'),
            array('/menus.html', 'menus'),
            array('/menus', 'menus'),
            array('menus/5/', 'menus'),
            array('menus/create', 'menus'),
            array('menus/5/create', 'menus/create'),
            array('menus/5/index', 'menus'),
            array('menus/:id/block', 'menus'),
            array('menus/block', 'menus'),
            array('menus/index/items', 'menus/items'),
            array('menus/create/items', 'menus/items'),
            array('menus/index/items/index', 'menus/items'),
        );
    }

    /**
     * @dataProvider routeProvider
     */
    public function testRoute($resource, $method, $expected)
    {
        $route = Url::route($resource, $method);
        $this->assertEquals($expected, $route);
    }

    public function routeProvider()
    {
        return array(
            array('menus', 'get', array('menus', 'index', array())),
            array('menus', 'put', array('menus', 'update', array())),
            array('menus', 'post', array('menus', 'store', array())),
            array('menus', 'delete', array('menus', 'destroy', array())),
            array('menus/5', 'get', array('menus', 'show', array('id' => 5))),
            array('menus/index', 'put', array('menus', 'update', array('id' => 'index'))),
            array('menus/5', 'post', array('menus', 'store', array('id' => 5))),
            array('menus/5', 'delete', array('menus', 'destroy', array('id' => 5))),
            array('menus/create', 'get', array('menus', 'create', array())),
            array('menus/:id/block', 'get', array('menus', 'block', array('id' => ':id'))),
            array('menus/block', 'get', array('menus', 'block', array())),
            array('menus/5/items.admin', 'get', array('menus/items', 'index', array('pid' => 5))),
            array('menus/5/items/10.admin', 'get', array('menus/items', 'show', array('id' => 10, 'pid' => 5))),
            array('menus/5/items.admin', 'put', array('menus/items', 'update', array('pid' => 5))),
            array('menus/5/items/10.admin', 'get', array('menus/items', 'show', array('id' => 10, 'pid' => 5))),
            array('menus/5/items/dzen/relations.admin', 'get', array('menus/items/relations', 'index', array('pid' => 'dzen', 'ppid' => 5))),
            array('settings/demo-pane', 'get', array('settings', 'show', array('id' => 'demo-pane'))),
        );
    }

    /**
     * @dataProvider pregProvider
     */
    public function testPreg($pattern, $expected)
    {
        $this->assertEquals($expected, Url::preg($pattern));
    }

    public function pregProvider()
    {
        return array(
            array('menus/:menu_id/items/:menu_id_item_id', array('menus\/([^\/]*)\/items\/([^\/]*)', array('menu_id', 'menu_id_item_id'))),
            array('menus/:menu_id/items', array('menus\/([^\/]*)\/items', array(0 => 'menu_id'))),
            array('menus/:menu_id', array('menus\/([^\/]*)', array(0 => 'menu_id'))),
            array('menus', array('menus', array())),
        );
    }

    /**
     * @dataProvider parseProvider
     */
    public function testParse($resource, $pattern, $expected)
    {
        $parsed = [];
        Url::parse($resource, $pattern, $parsed);
        $this->assertEquals($expected, $parsed);
    }

    public function parseProvider()
    {
        return array(
            array('menus/5/items/10', 'menus/:menu_id/items/:menu_id_item_id', array('menu_id' => 5, 'menu_id_item_id' => 10)),
        );
    }

    /**
     * @dataProvider resourceProvider
     */
    public function testResoute($pattern, $parsed, $expected)
    {
        $this->assertEquals($expected, Url::resource($pattern, $parsed));
    }

    public function resourceProvider()
    {
        return array(
            array('menus/:menu_id/items/:menu_id_item_id', array('menu_id' => 5, 'menu_id_item_id' => 10), 'menus/5/items/10',),
            array('menus/:menu_id/items/:menu_id', array('menu_id' => 5), 'menus/5/items/5',),
        );
    }
    
    public function testParent()
    {
        $_SERVER['REQUEST_URI'] = 'warehouses/2/deposit-activities/create';
        $this->assertEquals('warehouses/2/deposit-activities/create', Url::resource('$', array('param' => '123')));
        $this->assertEquals('warehouses/2/addresses', Url::resource('$$$/addresses', array('param' => '123')));
    }

}
