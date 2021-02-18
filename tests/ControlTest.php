<?php

use PHPUnit\Framework\TestCase;

class ControlTest extends TestCase
{

    public function testControl()
    {
        
        $link = new Pina\Controls\LinkedButton;
        $link->setTitle('Title');
        $link->setLink('http://mywebsite.com/some-page');
        
        $this->assertEquals('<a class="btn btn-default" href="http://mywebsite.com/some-page">Title</a>', $link->draw());
        
    }
    
}