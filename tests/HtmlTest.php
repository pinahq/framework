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
        $r = Html::nest('div/table/tr/td', 'hello!');
        $this->assertEquals('<div><table><tr><td>hello!</td></tr></table></div>', $r);
        $r = Html::nest('div/table#some-id sss.my-class other_class/tr/td', 'hello!');
        $this->assertEquals('<div><table id="some-id sss" class="my-class other_class"><tr><td>hello!</td></tr></table></div>', $r);
        $r = Html::nest('div/table#some-id#sss.my-class.other_class/tr/td', 'hello!');
        $this->assertEquals('<div><table id="some-id sss" class="my-class other_class"><tr><td>hello!</td></tr></table></div>', $r);
    }

}
