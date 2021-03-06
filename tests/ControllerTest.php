<?php

use PHPUnit\Framework\TestCase;
use Pina\Components\RecordFormComponent;
use Pina\Components\Schema;
use Pina\Controls\Form;
use Pina\Controls\FormInput;
use Pina\Controls\FormStatic;
use Pina\Controls\Paragraph;
use Pina\CronEventEndpoint;
use Pina\App;
use Pina\CSRF;

class ControllerTest extends TestCase
{

    public function test()
    {
        App::init('test', __DIR__ . '/config');
        $data = [
            [
                'id' => 1,
                'event' => 'order.paid',
                'data' => '123',
                'priority' => '1',
                'created' => '2020-01-02 03:04:05'
            ],
            [
                'id' => 2,
                'event' => 'order.canceled',
                'data' => '124',
                'priority' => '0',
                'created' => '2020-01-02 04:05:06'
            ],
            [
                'id' => 3,
                'event' => 'order.returned',
                'data' => '125',
                'priority' => '1',
                'created' => '2020-01-02 05:06:07'
            ],
        ];

        Pina\CronEventGateway::instance()->truncate();
        Pina\CronEventGateway::instance()->insert($data);


        $expectedHtml = '<div class="card"><div class="card-body">'
            . '<table class="table table-hover">'
            . '<tr><th>Event</th><th>Data</th><th>Priority</th><th>Created at</th></tr>'
            . '<tr><td>order.paid</td><td>123</td><td>1</td><td>2020-01-02 03:04:05</td></tr>'
            . '<tr><td>order.canceled</td><td>124</td><td>0</td><td>2020-01-02 04:05:06</td></tr>'
            . '<tr><td>order.returned</td><td>125</td><td>1</td><td>2020-01-02 05:06:07</td></tr>'
            . '</table>'
            . '</div></div>';

        $endpoint = new CronEventEndpoint();
        $html = $endpoint->index()->forgetField('id')->drawWithWrappers();
        $this->assertEquals($expectedHtml, $html);

        $id = Pina\CronEventGateway::instance()->id();

        $expectedRowHtml = '<div class="card"><div class="card-body">'
            . '<div class="form-group"><label class="control-label">Event</label><p class="form-control-static">order.paid</p></div>'
            . '<div class="form-group"><label class="control-label">Data</label><p class="form-control-static">123</p></div>'
            . '<div class="form-group"><label class="control-label">Priority</label><p class="form-control-static">1</p></div>'
            . '<div class="form-group"><label class="control-label">Created at</label><p class="form-control-static">2020-01-02 03:04:05</p></div>'
            . '</div></div>';

        $html = $endpoint->show($id)->drawWithWrappers();
        $this->assertEquals($expectedRowHtml, $html);

        $router = App::router();
        $router->register('cron-events', CronEventEndpoint::class);
        $router->register('lk/:profile_id/cron-events', CronEventEndpoint::class);

        $html = $router->run("cron-events", 'get')->forgetField('id')->drawWithWrappers();
        $this->assertEquals($expectedHtml, $html);
        $html = $router->run("lk/1/cron-events", 'get')->forgetField('id')->drawWithWrappers();
        $this->assertEquals($expectedHtml, $html);
        $this->assertEmpty($router->run("lk/1/cron-events/2/active-triggers", 'get')->drawWithWrappers());


        $form = (new RecordFormComponent)
            ->basedOn($router->run("lk/1/cron-events/" . $id, 'get'))
            ->forgetField('id')
            ->setMethod('PUT')
            ->setAction("lk/1/cron-events/" . $id);

        $cl = $form->getFormClass();

        $expectedRowEditHtml = ''
            . '<form class="' . $cl . ' form pina-form" action="/put!lk/1/cron-events/' . $id . '" method="post">'
            . CSRF::formField('PUT')
            . '<div class="card"><div class="card-body">'
            . '<div class="form-group"><label class="control-label">Event</label><input type="text" class="form-control" name="event" value="order.paid"></div>'
            . '<div class="form-group"><label class="control-label">Data</label><textarea class="form-control" name="data" rows="3">123</textarea></div>'
            . '<div class="form-group"><label class="control-label">Priority</label><input type="text" class="form-control" name="priority" value="1"></div>'
            . '<div class="form-group"><label class="control-label">Created at</label><input type="text" class="form-control" name="created" value="2020-01-02 03:04:05"></div>'
            . '</div></div>'
            . '<button type="submit" class="btn btn-primary">Сохранить</button>'
            . '</form>';


        $this->assertEquals($expectedRowEditHtml, $form->drawWithWrappers());


        /** @var RecordFormComponent $component */
        $component = $router->run("lk/1/cron-events/" . $id, 'get')
            ->forgetField('id')
            ->turnTo('form')
            ->setMethod("PUT")
            ->setAction("lk/1/cron-events/" . $id);

        $cl = $component->getFormClass();

        $expectedWrapHtml = ''
            . '<form class="' . $cl . ' form pina-form" action="/put!lk/1/cron-events/' . $id . '" method="post">'
            . CSRF::formField('PUT')
            . '<div class="card"><div class="card-body">'
            . '<div class="form-group"><label class="control-label">Event</label><input type="text" class="form-control" name="event" value="order.paid"></div>'
            . '<div class="form-group"><label class="control-label">Data</label><textarea class="form-control" name="data" rows="3">123</textarea></div>'
            . '<div class="form-group"><label class="control-label">Priority</label><input type="text" class="form-control" name="priority" value="1"></div>'
            . '<div class="form-group"><label class="control-label">Created at</label><input type="text" class="form-control" name="created" value="2020-01-02 03:04:05"></div>'
            . '</div></div>'
            . '<button type="submit" class="btn btn-primary">Сохранить</button>'
            . '</form>';

        App::container()->set(FormStatic::class, FormInput::class);
        $this->assertEquals($expectedWrapHtml, $component->drawWithWrappers());
        App::container()->set(FormStatic::class, FormStatic::class);

        $component = $router->run("lk/1/cron-events/" . $id, 'get')->forgetField('id');
        $component->wrap(new Pina\Controls\TableCell);
        $component->wrap(new Pina\Controls\TableRow);
        $component->wrap(new Pina\Controls\Table);
        $note = (new Paragraph)->setText('note');
        $form = (new Form)->setAction('/')->setMethod('delete');
        $form->append($note);
        $component->wrap($form);

        $expectedWrapHtml = '<form action="/delete!" method="post">'
            . CSRF::formField('delete')
            . '<table><tr><td>'
            . '<div class="card"><div class="card-body">'
            . '<div class="form-group"><label class="control-label">Event</label><p class="form-control-static">order.paid</p></div>'
            . '<div class="form-group"><label class="control-label">Data</label><p class="form-control-static">123</p></div>'
            . '<div class="form-group"><label class="control-label">Priority</label><p class="form-control-static">1</p></div>'
            . '<div class="form-group"><label class="control-label">Created at</label><p class="form-control-static">2020-01-02 03:04:05</p></div>'
            . '</div></div>'
            . '</td></tr></table>'
            . '<p>note</p>'
            . '</form>';

        $this->assertEquals($expectedWrapHtml, $component->drawWithWrappers());

        $expectedWrapHtml = '<table><tr><td>'
            . '<div class="card"><div class="card-body">'
            . '<div class="form-group"><label class="control-label">Event</label><p class="form-control-static">order.paid</p></div>'
            . '<div class="form-group"><label class="control-label">Data</label><p class="form-control-static">123</p></div>'
            . '<div class="form-group"><label class="control-label">Priority</label><p class="form-control-static">1</p></div>'
            . '<div class="form-group"><label class="control-label">Created at</label><p class="form-control-static">2020-01-02 03:04:05</p></div>'
            . '</div></div>'
            . '</td></tr></table>';

        $this->assertEquals($expectedWrapHtml, $component->unwrap()->drawWithWrappers());

        $r = $router->run("lk/1/cron-events", 'delete');
        $class = new ReflectionClass($r);
        $prop = $class->getProperty('code');
        $prop->setAccessible(true);
        $this->assertEquals('400 Bad Request', $prop->getValue($r));

        $r = $router->run("lk/1/cron-events/" . $id, 'delete');
        $class = new ReflectionClass($r);
        $prop = $class->getProperty('code');
        $prop->setAccessible(true);
        $this->assertEquals('200 OK', $prop->getValue($r));
    }

    public function testHidden()
    {
        $schema = new Schema();
        $schema->add('mode', 'title', 'hidden');
        $form = new RecordFormComponent();
        $form->load(['mode' => 'test'], $schema);
        $r = $form->drawWithWrappers();

        $cl = $form->getFormClass();

        $expected = '<form class="' . $cl . ' form pina-form" action="" method="get">'
            . '<div class="card"><div class="card-body">'
            . '<input type="hidden" name="mode" value="test">'
            . '</div></div>'
            . '<button type="submit" class="btn btn-primary">Сохранить</button>'
            . '</form>';
        $this->assertEquals($expected, $r);
    }

}
