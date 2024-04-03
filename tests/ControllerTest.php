<?php

use PHPUnit\Framework\TestCase;
use Pina\Data\DataRecord;
use Pina\Data\Schema;
use Pina\Controls\Form;
use Pina\Controls\FormInput;
use Pina\Controls\FormStatic;
use Pina\Controls\Paragraph;
use Pina\Controls\RecordForm;
use Pina\Controls\RecordView;
use Pina\Events\Cron\CronEventEndpoint;
use Pina\App;
use Pina\CSRF;
use Pina\Http\Location;
use Pina\Http\Request;

class ControllerTest extends TestCase
{

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function test()
    {
        App::init('test', __DIR__ . '/config');

        \Pina\Request::push(new \Pina\RequestHandler('lk/1/cron-events', 'index', []));
        $_SERVER['HTTP_HOST'] = 'test.com';

        $data = [
            [
                'id' => 1,
                'event' => 'order.paid',
                'data' => '123',
                'priority' => '1',
                'delay' => '0',
                'worker_id' => null,
                'created_at' => '2020-01-02 03:04:05',
                'scheduled_at' => null,
                'started_at' => null,
            ],
            [
                'id' => 2,
                'event' => 'order.canceled',
                'data' => '124',
                'priority' => '2',
                'delay' => '0',
                'worker_id' => null,
                'created_at' => '2020-01-02 04:05:06',
                'scheduled_at' => null,
                'started_at' => null,
            ],
            [
                'id' => 3,
                'event' => 'order.returned',
                'data' => '125',
                'priority' => '1',
                'delay' => '0',
                'worker_id' => null,
                'created_at' => '2020-01-02 05:06:07',
                'scheduled_at' => null,
                'started_at' => null,
            ],
        ];

        Pina\Events\Cron\CronEventGateway::instance()->truncate();
        Pina\Events\Cron\CronEventGateway::instance()->insert($data);
        $tableContent = '';
        foreach ($data as $k => $v) {
            $data[$k]['id'] = Pina\Events\Cron\CronEventGateway::instance()
                ->whereBy('created_at', $v['created_at'])
                ->id();

            $url = App::link('lk/1/cron-events/:id', ['id' => $data[$k]['id']]);

            $data[$k]['scheduled_at'] = $data[$k]['created_at'];
            $data[$k]['started_at'] = '';
            $data[$k]['worker_id'] = '-';
            $tableContent .= '<tr>' . implode(
                    array_map(function ($a) use ($url) {
                        return '<td><a href="'.$url.'">' . $a . '</a></td>';
                    }, $data[$k])
                ) . '</tr>';
        }

        $expectedHtml = '<div class="card"><div class="card-body">'
            . '<table class="table table-hover">'
            . '<tr><th>ID</th><th>Event</th><th>Data</th><th>Priority</th><th>Delay</th><th>Worker ID</th><th>Created at</th><th>Scheduled at</th><th>Started at</th></tr>'
            . $tableContent
            . '</table>'
            . '</div></div>';


        $request = new Request($_GET, [], [], $_COOKIE, $_FILES, $_SERVER);
        $request->setLocation(new Location('/lk/1/cron-events'), new Location('/lk/1/cron-events'));

        $endpoint = new CronEventEndpoint($request);
        $r = $endpoint->index();
        $this->assertEquals($expectedHtml, (string)$r);

        $id = Pina\Events\Cron\CronEventGateway::instance()->id();

        $removeButton = '<a class="pina-action btn btn-default" href="#" data-resource="lk/1/cron-events" data-method="delete" data-params="" data-csrf-token="'.CSRF::token().'">Удалить</a>';

        $expectedRowHtml = '<div class="card"><div class="card-body">'
            . $this->getStaticFormInner()
            . '</div></div>' . $removeButton;

        $r = $endpoint->show($id);
        $this->assertEquals($expectedRowHtml, (string)$r);

        $router = App::router();
//        $router->register('cron-events', CronEventEndpoint::class);
        $router->register('lk/:profile_id/cron-events', CronEventEndpoint::class);

//        $html = $router->run("cron-events", 'get')->drawWithWrappers();
//        $this->assertEquals($expectedHtml, $html);
        $html = $router->run("lk/1/cron-events", 'get')->drawWithWrappers();
        $this->assertEquals($expectedHtml, $html);
        $this->assertEmpty($router->run("lk/1/cron-events/2/active-triggers", 'get')->drawWithWrappers());


        /** @var RecordView $r */
        $r = $router->run("lk/1/cron-events/" . $id, 'get');

        $form = (new RecordForm)
            ->load($r->getPayload())
            ->setMethod('PUT')
            ->setAction("lk/1/cron-events/" . $id);

        $cl = $form->getFormClass();

        $expectedRowEditHtml = ''
            . '<form class="' . $cl . ' form pina-form" action="/put!lk/1/cron-events/' . $id . '" method="post">'
            . CSRF::formField('PUT')
            . '<div class="card"><div class="card-body">'
            . $this->getEditFormInner()
            . '</div></div>'
            . '<button type="submit" class="btn btn-primary">Сохранить</button>'
            . '</form>';


        $this->assertEquals($expectedRowEditHtml, (string)$form);

        /** @var RecordView $view */
        $view = $router->run("lk/1/cron-events/" . $id, 'get');
        $form = new RecordForm();
        $form->load($view->getPayload())
            ->setMethod("PUT")
            ->setAction("lk/1/cron-events/" . $id);

        $cl = $form->getFormClass();

        $expectedWrapHtml = ''
            . '<form class="' . $cl . ' form pina-form" action="/put!lk/1/cron-events/' . $id . '" method="post">'
            . CSRF::formField('PUT')
            . '<div class="card"><div class="card-body">'
            . $this->getForcedEditFormInner()
            . '</div></div>'
            . '<button type="submit" class="btn btn-primary">Сохранить</button>'
            . '</form>';

        App::container()->set(FormStatic::class, FormInput::class);
        $this->assertEquals($expectedWrapHtml, (string)$form);
        App::container()->set(FormStatic::class, FormStatic::class);

        $r = $router->run("lk/1/cron-events/" . $id, 'get');
        $r->wrap(new Pina\Controls\TableCell);
        $r->wrap(new Pina\Controls\TableRow);
        $r->wrap(new Pina\Controls\Table);
        $note = (new Paragraph)->setText('note');
        $form = (new Form)->setAction('/')->setMethod('delete');
        $form->append($note);
        $r->wrap($form);

        $removeButton = '<a class="pina-action btn btn-default" href="#" data-resource="lk/1/cron-events/'.$id.'" data-method="delete" data-params="" data-csrf-token="'.CSRF::token().'">Удалить</a>';

        $expectedWrapHtml = '<form action="/delete!" method="post">'
            . CSRF::formField('delete')
            . '<p>note</p>'
            . '<table><tr><td>'
            . '<div class="card"><div class="card-body">'
            . $this->getStaticFormInner()
            . '</div></div>'. $removeButton
            . '</td></tr></table>'
            . '</form>';

        $this->assertEquals($expectedWrapHtml, (string)$r);

        $expectedWrapHtml = '<table><tr><td>'
            . '<div class="card"><div class="card-body">'
            . $this->getStaticFormInner()
            . '</div></div>'. $removeButton
            . '</td></tr></table>';

        $this->assertEquals($expectedWrapHtml, (string)$r->unwrap());

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

        \Pina\Request::pop();
    }

    /**
     * @throws Exception
     */
    public function testHidden()
    {
        $schema = new Schema();
        $schema->add('mode', 'title', 'string')->setHidden();
        $form = new RecordForm();
        $form->load(new DataRecord(['mode' => 'test'], $schema));
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

    private function getStaticFormInner()
    {
        return '<div class="form-group"><label class="control-label">Event</label><div class="form-control-static">order.paid</div></div>'
            . '<div class="form-group"><label class="control-label">Data</label><div class="form-control-static">123</div></div>'
            . '<div class="form-group"><label class="control-label">Priority</label><div class="form-control-static">1</div></div>'
            . '<div class="form-group"><label class="control-label">Delay</label><div class="form-control-static">0</div></div>'
            . '<div class="form-group"><label class="control-label">Worker ID</label><div class="form-control-static">-</div></div>'
            . '<div class="form-group"><label class="control-label">Created at</label><div class="form-control-static">2020-01-02 03:04:05</div></div>'
            . '<div class="form-group"><label class="control-label">Scheduled at</label><div class="form-control-static">2020-01-02 03:04:05</div></div>'
            . '<div class="form-group"><label class="control-label">Started at</label><div class="form-control-static"></div></div>';
    }

    private function getEditFormInner()
    {
        return '<div class="form-group"><label class="control-label">Event *</label><input type="text" class="form-control" name="event" value="order.paid"></div>'
            . '<div class="form-group"><label class="control-label">Data</label><textarea class="form-control" name="data" rows="3">123</textarea></div>'
            . '<div class="form-group"><label class="control-label">Priority</label><input type="text" class="form-control" name="priority" value="1"></div>'
            . '<div class="form-group"><label class="control-label">Delay</label><input type="text" class="form-control" name="delay" value="0"></div>'
            . '<div class="form-group"><label class="control-label">Worker ID</label><input type="text" class="form-control" name="worker_id"></div>'
            . '<div class="form-group"><label class="control-label">Created at</label><div class="form-control-static">2020-01-02 03:04:05</div></div>'
            . '<div class="form-group"><label class="control-label">Scheduled at</label><input type="text" class="form-control" name="scheduled_at" value="2020-01-02 03:04:05"></div>'
            . '<div class="form-group"><label class="control-label">Started at</label><input type="text" class="form-control" name="started_at"></div>';
    }
    private function getForcedEditFormInner()
    {
        return '<div class="form-group"><label class="control-label">Event *</label><input type="text" class="form-control" name="event" value="order.paid"></div>'
            . '<div class="form-group"><label class="control-label">Data</label><textarea class="form-control" name="data" rows="3">123</textarea></div>'
            . '<div class="form-group"><label class="control-label">Priority</label><input type="text" class="form-control" name="priority" value="1"></div>'
            . '<div class="form-group"><label class="control-label">Delay</label><input type="text" class="form-control" name="delay" value="0"></div>'
            . '<div class="form-group"><label class="control-label">Worker ID</label><input type="text" class="form-control" name="worker_id"></div>'
            . '<div class="form-group"><label class="control-label">Created at</label><input type="text" class="form-control" name="created_at" value="2020-01-02 03:04:05"></div>'
            . '<div class="form-group"><label class="control-label">Scheduled at</label><input type="text" class="form-control" name="scheduled_at" value="2020-01-02 03:04:05"></div>'
            . '<div class="form-group"><label class="control-label">Started at</label><input type="text" class="form-control" name="started_at"></div>';
    }

}
