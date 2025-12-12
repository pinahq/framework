<?php

use PHPUnit\Framework\TestCase;
use Pina\App;
use Pina\CSRF;
use Pina\Legacy\RequestHandler;
use Pina\Legacy\Route;
use Pina\Module;

class CSRFTest extends TestCase
{

    public function testPermit()
    {
        App::env('test');
        
        $module = new Module;
        $routes = CSRF::whitelist(['request', 'cp/products']);

        foreach ($routes as $route) {
            Route::router()->own($route, $module);
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
        Route::router()->own('cp/products/prices', $module);
        $this->assertTrue(CSRF::verify($handler->controller(), $data));
    }

}
