<?php

use Pina\Url;
use Pina\Route;

class RouteTest extends PHPUnit_Framework_TestCase
{

    public function testResource()
    {
        Route::context("user_id", 7);
        $params = array('book_id' => 3);
        $r = Route::resource("users/:user_id/friends", $params);
        $this->assertEquals($r, "users/7/friends");
    }

}
