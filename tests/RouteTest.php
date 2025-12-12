<?php

use PHPUnit\Framework\TestCase;
use Pina\Legacy\Route;

class RouteTest extends TestCase
{

    public function testResource()
    {
        $params = array('book_id' => 3, 'user_id' => 7);
        $r = Route::resource("users/:user_id/friends", $params);
        $this->assertEquals($r, "users/7/friends");
    }
    
    public function testOwner()
    {
        Route::router()->own('/menus', new \Pina\Module());
        $module = Route::router()->owner('menus/items');
        $this->assertEquals(new \Pina\Module(), $module);
    }

}
