<?php

use PHPUnit\Framework\TestCase;
use Pina\App;

class ResourceManagerTest extends TestCase
{

    public function testJs()
    {
        App::init(__DIR__.'/config');
        
        App::assets()->addScriptContent("
            <script>
                alert('123');
                alert('234');
            </script>
        ");

        App::assets()->addScriptContent("
            <script>alert('234');</script>
        ");
        

        $this->assertEquals("
            <script>
                alert('123');
                alert('234');
            </script>
        \r\n
            <script>alert('234');</script>
        ", App::assets()->fetch('js'));

        App::assets()->startLayout();
        App::assets()->addScriptContent("<script>alert('!!!');</script>");

        $this->assertEquals("<script>alert('!!!');</script>\r\n
            <script>
                alert('123');
                alert('234');
            </script>
        \r\n
            <script>alert('234');</script>
        ", App::assets()->fetch('js'));

        App::assets()->addScript("http://github.com/123/123/test.js");
        App::assets()->addScript("/static/test.js");
        
        $this->assertEquals("<script>alert('!!!');</script>\r\n<script type=\"text/javascript\" src=\"http://github.com/123/123/test.js\"></script>\r\n<script type=\"text/javascript\" src=\"/static/test.js?1\"></script>\r\n
            <script>
                alert('123');
                alert('234');
            </script>
        \r\n
            <script>alert('234');</script>
        ", App::assets()->fetch('js'));
        
    }
    
    public function testCss()
    {
        App::init(__DIR__.'/config');
        
        $repeat = 0;

        App::assets()->addStyleContent("
            <style>
                body {background-color: black;}
                div {
                    color: white;
                }
            </style>
        ");

        App::assets()->addStyleContent("
            <style>h1{color: black;}</script>
        ");

        $this->assertEquals("
            <style>
                body {background-color: black;}
                div {
                    color: white;
                }
            </style>
        \r\n
            <style>h1{color: black;}</script>
        ", App::assets()->fetch('css'));

        App::assets()->startLayout();
        App::assets()->addStyleContent("<style>#id{display:hidden;}</style>");

        $this->assertEquals("<style>#id{display:hidden;}</style>\r\n
            <style>
                body {background-color: black;}
                div {
                    color: white;
                }
            </style>
        \r\n
            <style>h1{color: black;}</script>
        ", App::assets()->fetch('css'));

        App::assets()->addStyle("http://github.com/123/123/test.css");
        App::assets()->addStyle("/static/test.css");
        
        $this->assertEquals("<style>#id{display:hidden;}</style>\r\n<link href=\"http://github.com/123/123/test.css\" rel=\"stylesheet\">\r\n<link href=\"/static/test.css?1\" rel=\"stylesheet\">\r\n
            <style>
                body {background-color: black;}
                div {
                    color: white;
                }
            </style>
        \r\n
            <style>h1{color: black;}</script>
        ", App::assets()->fetch('css'));
        
    }


}
