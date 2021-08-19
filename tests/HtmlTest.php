<?php

use PHPUnit\Framework\TestCase;
use Pina\CSRF;
use Pina\Html;

class HtmlTest extends TestCase
{

    public function test()
    {

        $attributes = Html::getActionAttributes('get', 'products', ['category_id' => 5]);
        $this->assertEquals([
            'data-method' => 'get',
            'data-resource' => 'products',
            'data-params' => 'category_id=5',
            ], $attributes);

        $attributes = Html::getActionAttributes('post', 'products', ['category_id' => 5]);
        $this->assertEquals([
            'data-method' => 'post',
            'data-resource' => 'products',
            'data-params' => 'category_id=5',
            'data-csrf-token' => CSRF::token(),
            ], $attributes);
        
        $expectedHtml = '<a class="pina-action" href="#" data-method="post" data-resource="products" data-params="category_id=1" data-csrf-token="'.CSRF::token().'">Link</a>';
        $html = Html::a('Link', '#', ['class' => 'pina-action'] + Html::getActionAttributes('post', 'products', ['category_id' => 1]));
        $this->assertEquals($expectedHtml, $html);
    }

    public function testNest()
    {
//        $r = Html::zz('div.step(div.circle+div.round+p)', 10, 20);
        $r = Html::zz('.%(.circle+.round%+p)+span%', 'step', 10, 20);
        $this->assertEquals('<div class="step"><div class="circle"></div><div class="round">10</div><p></p></div><span>20</span>', $r);

        $r = Html::zz('div.%(div.circle+div.round%+p)+span%', 'step', 10, 20);
        $this->assertEquals('<div class="step"><div class="circle"></div><div class="round">10</div><p></p></div><span>20</span>', $r);

        $r = Html::nest('div.step/div.circle+p', 10);
        $this->assertEquals('<div class="step"><div class="circle"></div><p>10</p></div>', $r);
        $r = Html::nest('div.step/div.circle+p/span.before+%', 10);
        $this->assertEquals('<div class="step"><div class="circle"></div><p><span class="before"></span>10</p></div>', $r);
        $r = Html::nest('div/span[data-name=test]', 'hello!');
        $this->assertEquals('<div><span data-name="test">hello!</span></div>', $r);
        $r = Html::nest('div/span[disabled]', 'hello!');
        $this->assertEquals('<div><span disabled="disabled">hello!</span></div>', $r);
        $r = Html::nest('div/table/tr/td', 'hello!');
        $this->assertEquals('<div><table><tr><td>hello!</td></tr></table></div>', $r);
        $r = Html::nest('div/table#some-id sss.my-class other_class/tr/td', 'hello!');
        $this->assertEquals('<div><table id="some-id sss" class="my-class other_class"><tr><td>hello!</td></tr></table></div>', $r);
        $r = Html::nest('div/table#some-id#sss.my-class.other_class/tr/td', 'hello!');
        $this->assertEquals('<div><table id="some-id sss" class="my-class other_class"><tr><td>hello!</td></tr></table></div>', $r);
        $r = Html::nest('div#first/table#some-id#sss.my-class.other_class/tr/td.last', 'hello!');
        $this->assertEquals('<div id="first"><table id="some-id sss" class="my-class other_class"><tr><td class="last">hello!</td></tr></table></div>', $r);
        $r = Html::nest('div#first/table#some-id#sss.my-class.other_class[disabled][data-id=8]/tr/td.last', 'hello!');
        $this->assertEquals('<div id="first"><table id="some-id sss" class="my-class other_class" disabled="disabled" data-id="8"><tr><td class="last">hello!</td></tr></table></div>', $r);
    }

}
