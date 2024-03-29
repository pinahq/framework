<?php

use PHPUnit\Framework\TestCase;
use Pina\Controls\Card;
use Pina\Controls\Wrapper;

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
        $this->assertEquals($expected . $anotherLink, $link->drawWithWrappers());

        $link->before($anotherLink);
        $this->assertEquals($anotherLink . $expected . $anotherLink, $link->drawWithWrappers());

        $card = new Card();
        $link->wrap($card);

        $expected2 = '<div class="card"><div class="card-body">'
            . $anotherLink . $expected . $anotherLink
            . '</div></div>';

        $this->assertEquals($expected2, $link->drawWithWrappers());

        $card->after($anotherLink);
        $expected3 = '<div class="card"><div class="card-body">'
            . $anotherLink . $expected . $anotherLink
            . '</div></div>'
            . $anotherLink;

        $this->assertEquals($expected3, $link->drawWithWrappers());
    }

    public function testWrapper()
    {
        $link = new Pina\Controls\LinkedButton;
        $link->setTitle('Title');
        $link->setLink('http://mywebsite.com/some-page');
        $expected = '<div id="my" class="card card-primary"><div class="card-body">'
            . $link->drawWithWrappers()
            . '</div></div>';
        $wrapper = new Wrapper('.card#my/.card-body');
        $wrapper->addClass('card-primary');
        $link->wrap($wrapper);
        $this->assertEquals($expected, $link->drawWithWrappers());
    }

    public function testAttribute()
    {
        $icon = new Wrapper('.icon');
        $icon->setAttribute('title', 'Display');
        $this->assertEquals('<div class="icon" title="Display"></div>', $icon->__toString());

        $icon = new Wrapper('.icon');
        $icon->addClass('test');
        $icon->addClass('test');
        $icon->addClass('test2');
        $this->assertEquals('<div class="icon test test2"></div>', $icon->__toString());

        $icon->removeClass('test');
        $this->assertEquals('<div class="icon test2"></div>', $icon->__toString());

        $icon = new Wrapper('.icon');
        $icon->addClass('test   test2');
        $this->assertEquals('<div class="icon test test2"></div>', $icon->__toString());

        $icon->removeClass('test');
        $this->assertEquals('<div class="icon test2"></div>', $icon->__toString());
    }

}