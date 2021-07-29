<?php

use PHPUnit\Framework\TestCase;
use Pina\Controls\Card;

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

}