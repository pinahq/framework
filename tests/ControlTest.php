<?php

use PHPUnit\Framework\TestCase;

class ControlTest extends TestCase
{

    public function testControl()
    {
        
        $link = new Pina\Controls\LinkedButton;
        $link->setTitle('Title');
        $link->setLink('http://mywebsite.com/some-page');

        $expected = '<a class="btn btn-default" href="http://mywebsite.com/some-page">Title</a>';

        $this->assertEquals($expected, $link->drawWithWrappers());

        $anotherLink = clone $link;
        $link->after($anotherLink);
        $this->assertEquals($expected . $anotherLink->draw(), $link->drawWithWrappers());

        $link->before($anotherLink);
        $this->assertEquals($anotherLink->draw() . $expected . $anotherLink->draw(), $link->drawWithWrappers());


    }
    
}