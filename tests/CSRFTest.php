<?php

use PHPUnit\Framework\TestCase;
use Pina\Module;
use Pina\CSRF;
use Pina\App;
use Pina\Route;
use Pina\RequestHandler;

class CSRFTest extends TestCase
{

    public function testPermit()
    {
        App::env('test');
        
        $module = new Module;
        $routes = CSRF::whitelist(['/request', 'cp/products']);

        foreach ($routes as $route) {
            Route::own($route, $module);
        }
        
        $data = [];
        
        $_SERVER["REQUEST_METHOD"] = 'POST';
        $_SERVER["REQUEST_URI"] = '/request';
        $handler = new RequestHandler('request', 'post', $data);
        $this->assertTrue(CSRF::verify($handler->controller(), $data));
        
        $_SERVER["REQUEST_METHOD"] = 'POST';
        $_SERVER["REQUEST_URI"] = '/cp/ru/products/1/prices';
        $handler = new RequestHandler('cp/ru/products/1/prices', 'post', $data);
        
        $this->assertTrue(CSRF::verify($handler->controller(), $data));
        Route::own('cp/products/prices', $module);
        $this->assertFalse(CSRF::verify($handler->controller(), $data));
    }

}
