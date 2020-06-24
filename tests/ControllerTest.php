<?php

use PHPUnit\Framework\TestCase;
use Pina\CronEventEndpoint;
use Pina\App;
use Pina\Request;

class ControllerTest extends TestCase
{

    public function test()
    {
        App::init('test', __DIR__.'/config');
        $data = [
            ['id' => 1, 'event' => 'order.paid', 'created' => '2020-01-02 03:04:05'],
            ['id' => 2, 'event' => 'order.canceled', 'created' => '2020-01-02 04:05:06'],
            ['id' => 3, 'event' => 'order.returned', 'created' => '2020-01-02 05:06:07'],
        ];
        
        Pina\CronEventGateway::instance()->truncate();
        Pina\CronEventGateway::instance()->insert($data);
        
        $endpoint = new CronEventEndpoint();
        $html = $endpoint->index([])->forgetColumn('id')->draw();
        $this->assertEquals(
            '<table>'
            . '<tr><th>Событие</th><th>Дата создания</th></tr>'
            . '<tr><td>order.paid</td><td>2020-01-02 03:04:05</td></tr>'
            . '<tr><td>order.canceled</td><td>2020-01-02 04:05:06</td></tr>'
            . '<tr><td>order.returned</td><td>2020-01-02 05:06:07</td></tr>'
            . '</table>', $html
        );
    }

}
