<?php

use PHPUnit\Framework\TestCase;
use Pina\Components\ListData;
use Pina\Components\TableComponent;
use Pina\Components\ListComponent;
use Pina\Components\Schema;

class DataTest extends TestCase
{

    public function test()
    {
        $schema = new Schema();
        $schema->add('id', '#');
        $schema->add('event', 'Событие');
        $schema->add('created_at', 'Дата создания');

        $data = [
            ['id' => 1, 'event' => 'order.paid', 'created_at' => '2020-01-02 03:04:05'],
            ['id' => 2, 'event' => 'order.canceled', 'created_at' => '2020-01-02 04:05:06'],
            ['id' => 3, 'event' => 'order.returned', 'created_at' => '2020-01-02 05:06:07'],
        ];

        $expectedHtml = '<div class="card"><div class="card-body">'
            . '<table class="table table-hover">'
            . '<tr><th>#</th><th>Событие</th><th>Дата создания</th></tr>'
            . '<tr><td>1</td><td>order.paid</td><td>2020-01-02 03:04:05</td></tr>'
            . '<tr><td>2</td><td>order.canceled</td><td>2020-01-02 04:05:06</td></tr>'
            . '<tr><td>3</td><td>order.returned</td><td>2020-01-02 05:06:07</td></tr>'
            . '</table>'
            . '</div></div>';

        $list = new ListData();
        $list->load($data, $schema);
        $html = (new TableComponent)->basedOn($list)->drawWithWrappers();
        $this->assertEquals($expectedHtml, $html);

        $html = (new ListComponent)->basedOn((new TableComponent)->basedOn($list))->select('event')->drawWithWrappers();
        $this->assertEquals(
            '<ul>'
            . '<li>order.paid</li>'
            . '<li>order.canceled</li>'
            . '<li>order.returned</li>'
            . '</ul>',
            $html
        );
        $this->assertEquals(
            (new ListComponent)->basedOn((new TableComponent)->basedOn($list))->select('event')->drawWithWrappers(),
            (new ListComponent)->basedOn($list)->select('event')->drawWithWrappers()
        );
        $this->assertEquals(
            (new ListComponent)->basedOn((new TableComponent)->basedOn($list))->select('id')->drawWithWrappers(),
            (new ListComponent)->basedOn($list)->select('id')->drawWithWrappers()
        );

    }

}
