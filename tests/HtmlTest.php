<?php

use PHPUnit\Framework\TestCase;
use Pina\CSRF;
use Pina\Html;
use Pina\Model\TagOptions;

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

    public function testNewAttributes()
    {
        $tagOptions = new TagOptions();
        $tagOptions->classes()->add('simple');
        $tagOptions->classes()->add('nav    bar');
        $tagOptions->set('data-method', 'post');
        $expected = [
            'class' => 'simple nav bar',
            'data-method' => 'post',
        ];
        $this->assertEquals($expected, $tagOptions->toArray());

        $this->assertTrue($tagOptions->classes()->has('nav'));
        $this->assertTrue($tagOptions->classes()->has('bar'));
        $this->assertTrue($tagOptions->classes()->has('simple'));
        $this->assertTrue($tagOptions->classes()->has('nav simple'));
        $this->assertFalse($tagOptions->classes()->has('nav2'));
        $this->assertFalse($tagOptions->classes()->has('nav2 simple'));

        $tagOptions->classes()->remove('nav');
        $this->assertFalse($tagOptions->classes()->has('nav simple'));

        $this->assertEquals('simple bar', $tagOptions->get('class'));
        $this->assertEquals('post', $tagOptions->get('data-method'));

    }

}
