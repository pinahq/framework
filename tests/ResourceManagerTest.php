<?php

use PHPUnit\Framework\TestCase;
use Pina\App;
use Pina\Url;
use Pina\Route;
use Pina\ResourceManager;

class ResourceManagerTest extends TestCase
{

    public function testJs()
    {
        App::init('test', __DIR__.'/config');
        
        $repeat = 0;
        $view = new \Pina\Templater;

        require_once __DIR__.'/../src/Pina/helpers/block.script.php';
        require_once __DIR__.'/../src/Pina/helpers/function.scripts.php';
        smarty_block_script(array(), "
            <script>
                alert('123');
                alert('234');
            </script>
        ", $view, $repeat);
        
        smarty_block_script(array(), "
            <script>alert('234');</script>
        ", $view, $repeat);
        

        $this->assertEquals("
            <script>
                alert('123');
                alert('234');
            </script>
        \r\n
            <script>alert('234');</script>
        ", smarty_function_scripts(array(), $view));

        App::container()->get(\Pina\ResourceManagerInterface::class)->startLayout();
        smarty_block_script(array(), "<script>alert('!!!');</script>", $view, $repeat);

        $this->assertEquals("<script>alert('!!!');</script>\r\n
            <script>
                alert('123');
                alert('234');
            </script>
        \r\n
            <script>alert('234');</script>
        ", smarty_function_scripts(array(), $view));
        
        smarty_block_script(array('src' => "http://github.com/123/123/test.js"), '', $view, $repeat);
        smarty_block_script(array('src' => "/static/test.js"), '', $view, $repeat);
        
        $this->assertEquals("<script>alert('!!!');</script>\r\n<script src=\"http://github.com/123/123/test.js\" type=\"text/javascript\"></script>\r\n<script src=\"/static/test.js?1\" type=\"text/javascript\"></script>\r\n
            <script>
                alert('123');
                alert('234');
            </script>
        \r\n
            <script>alert('234');</script>
        ", smarty_function_scripts(array(), $view));
        
    }
    
    public function testCss()
    {
        App::init('test', __DIR__.'/config');
        
        $repeat = 0;
        $view = new \Pina\Templater;

        require_once __DIR__.'/../src/Pina/helpers/block.style.php';
        require_once __DIR__.'/../src/Pina/helpers/function.styles.php';
        smarty_block_style(array(), "
            <style>
                body {background-color: black;}
                div {
                    color: white;
                }
            </style>
        ", $view, $repeat);

        smarty_block_style(array(), "
            <style>h1{color: black;}</script>
        ", $view, $repeat);

        $this->assertEquals("
            <style>
                body {background-color: black;}
                div {
                    color: white;
                }
            </style>
        \r\n
            <style>h1{color: black;}</script>
        ", smarty_function_styles(array(), $view));

        App::container()->get(\Pina\ResourceManagerInterface::class)->startLayout();
        smarty_block_style(array(), "<style>#id{display:hidden;}</style>", $view, $repeat);

        $this->assertEquals("<style>#id{display:hidden;}</style>\r\n
            <style>
                body {background-color: black;}
                div {
                    color: white;
                }
            </style>
        \r\n
            <style>h1{color: black;}</script>
        ", smarty_function_styles(array(), $view));
        
        smarty_block_style(array('src' => "http://github.com/123/123/test.css"), '', $view, $repeat);
        smarty_block_style(array('src' => "/static/test.css"), '', $view, $repeat);
        
        $this->assertEquals("<style>#id{display:hidden;}</style>\r\n<link rel=\"stylesheet\" href=\"http://github.com/123/123/test.css\" />\r\n<link rel=\"stylesheet\" href=\"/static/test.css?1\" />\r\n
            <style>
                body {background-color: black;}
                div {
                    color: white;
                }
            </style>
        \r\n
            <style>h1{color: black;}</script>
        ", smarty_function_styles(array(), $view));
        
    }


}
