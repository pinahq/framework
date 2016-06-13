<?php

use Pina\Input;

class InputTest extends PHPUnit_Framework_TestCase
{

    public function testGetHost()
    {
        $_SERVER["HTTP_HOST"] = 'test.com';
        $this->assertEquals('test.com', Input::getHost());
        
        unset($_SERVER["HTTP_HOST"]);
        $this->assertEquals('', Input::getHost());
    }
    
    public function testGetResource()
    {
        $_SERVER['SCRIPT_NAME'] = '/pina.php';
        $_SERVER['REQUEST_URI'] = '/pina.php?action=books/5';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_REQUEST['action'] = $_POST['action'] = 'books/5';
        $this->assertEquals('books/5', Input::getResource());
        
        $_REQUEST['action'] = $_POST['action'] = 'books/5.json';
        $this->assertEquals('books/5.json', Input::getResource());
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertEquals('get', Input::getMethod());
        
        $_REQUEST['action'] = $_GET['action'] = 'put!books/5.json';
        $this->assertEquals('get', Input::getMethod());
        
        $_REQUEST['action'] = $_GET['action'] = 'delete!books/5.json';
        $this->assertEquals('get', Input::getMethod());
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_REQUEST['action'] = $_POST['action'] = 'books/5.json';
        $this->assertEquals('post', Input::getMethod());
        
        $_REQUEST['action'] = $_POST['action'] = 'put!books/5.json';
        $this->assertEquals('put', Input::getMethod());
        
        $_REQUEST['action'] = $_POST['action'] = 'delete!books/5.json';
        $this->assertEquals('delete', Input::getMethod());
        
        
        $_SERVER['REQUEST_URI'] = 'books/6';
        $this->assertEquals('books/6', Input::getResource());
    }


}
