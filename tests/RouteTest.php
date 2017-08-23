<?php

use PHPUnit\Framework\TestCase;
use Pina\Url;
use Pina\Route;

class RouteTest extends TestCase
{

    public function testResource()
    {
        Route::context("user_id", 7);
        $params = array('book_id' => 3);
        $r = Route::resource("users/:user_id/friends", $params);
        $this->assertEquals($r, "users/7/friends");
    }
    
    /**
     * @dataProvider moduleProvider
     */
    public function testOwner($controller, $expected)
    {
        $module = Route::owner($controller);
        $this->assertEquals($expected, $module);
    }

    public function moduleProvider()
    {
        Route::own('/menus', 'Menus');
        return array(
            array('menus/items', 'Menus'),
            array('menus/', 'Menus'),
        );
    }


}
