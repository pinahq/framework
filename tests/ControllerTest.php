<?php

use PHPUnit\Framework\TestCase;
use Pina\App;
use Pina\Controls\Form\Form;
use Pina\Controls\Form\FormInput;
use Pina\Controls\Form\FormStatic;
use Pina\Controls\Form\Paragraph;
use Pina\Controls\Record\RecordForm;
use Pina\Controls\Record\RecordView;
use Pina\Data\DataRecord;
use Pina\Data\Schema;
use Pina\Http\Location;
use Pina\Http\Request;
use Pina\Queue\QueueEndpoint;

class ControllerTest extends TestCase
{

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function test()
    {
        App::init(__DIR__ . '/config');

        App::singletons()->set('base_url', new Location(''));

        $data = [
            [
                'id' => 1,
                'handler' => 'order.paid',
                'payload' => '123',
                'priority' => '1',
                'delay' => '0',
                'error' => '',
                'worker_id' => null,
                'created_at' => '2020-01-02 03:04:05',
                'scheduled_at' => null,
                'started_at' => null,
            ],
            [
                'id' => 2,
                'handler' => 'order.canceled',
                'payload' => '124',
                'priority' => '2',
                'delay' => '0',
                'error' => '',
                'worker_id' => null,
                'created_at' => '2020-01-02 04:05:06',
                'scheduled_at' => null,
                'started_at' => null,
            ],
            [
                'id' => 3,
                'handler' => 'order.returned',
                'payload' => '125',
                'priority' => '1',
                'delay' => '0',
                'error' => '',
                'worker_id' => null,
                'created_at' => '2020-01-02 05:06:07',
                'scheduled_at' => null,
                'started_at' => null,
            ],
        ];

        \Pina\Queue\QueueGateway::instance()->truncate();
        \Pina\Queue\QueueGateway::instance()->insert($data);
        $tableContent = '';
        foreach ($data as $k => $v) {
            $data[$k]['id'] = \Pina\Queue\QueueGateway::instance()
                ->whereBy('created_at', $v['created_at'])
                ->id();

            $url = App::link('lk/1/cron-events/:id', ['id' => $data[$k]['id']]);

            $data[$k]['scheduled_at'] = $data[$k]['created_at'];
            $data[$k]['started_at'] = '';
            $data[$k]['worker_id'] = '-';
            unset($data[$k]['error']);
            unset($data[$k]['created_at']);
            $tableContent .= '<tr>' . implode(
                    array_map(function ($a) use ($url) {
                        return '<td><a href="'.$url.'">' . $a . '</a></td>';
                    }, $data[$k])
                ) . '</tr>';
        }

        $expectedHtml = '<div class="row"><div class="col-lg-8"><div class="card"><div class="card-body">'
            . '<table class="table table-hover">'
            . '<tr><th>ID</th><th>Handler</th><th>Payload</th><th>Priority</th><th>Delay</th><th>Worker ID</th><th>Scheduled at</th><th>Started at</th></tr>'
            . $tableContent
            . '</table>'
            . '</div></div><div class="buttons"><a class="btn btn-primary" href="lk/1/cron-events/create">Добавить</a></div></div><div class="col-lg-4"><form class="fm6a9d9f360e46e form" action="" method="get"><div class="card"><div class="card-body"><div class="form-group"><label class="control-label">Поиск</label><input type="text" class="form-control" name="search"></div></div></div><div class="buttons row"><div class="col-sm-4"><button type="submit" class="btn btn-primary">Искать</button></div><div class="col-sm-8 text-right"><a class="btn btn-default" href="lk/1/cron-events">Сбросить</a></div></div></form><a class="btn btn-default" href="lk/1/cron-events/create">Добавить</a></div></div>';


        $request = new Request($_GET, [], [], $_COOKIE, $_FILES, $_SERVER);
        $request->setLocation(new Location('/lk/1/cron-events'), new Location('/lk/1/cron-events'));

        App::pushRequest($request);

        $endpoint = new QueueEndpoint();
        $r = $endpoint->index();
        $expectedHtml = preg_replace('/fm\w+\s/si', '', $expectedHtml);
        $r = preg_replace('/fm\w+\s/si', '', $r);
        $this->assertEquals($expectedHtml, (string)$r);

        $id = \Pina\Queue\QueueGateway::instance()->id();

        $removeButton = '<a class="pina-action btn btn-default" href="#" data-resource="lk/1/cron-events" data-method="delete" data-params="">Удалить</a>';

        $expectedRowHtml = '<div class="row"><div class="col-lg-8">'
            . '<div><div class="card"><div class="card-body">'
            . $this->getStaticFormInner($id)
            . '</div></div></div>'
            . '<div class="buttons row"><div class="col-sm-4"></div><div class="col-sm-8 text-right">' . $removeButton.'</div></div>'
            . '</div></div>'
        ;

        $r = $endpoint->show($id);
        $this->assertEquals($expectedRowHtml, (string)$r);

        $router = App::router();
//        $router->register('cron-events', CronEventEndpoint::class);
        $router->register('lk/:profile_id/cron-events', QueueEndpoint::class)->permit('public');
        App::access()->addGroup('public');

//        $html = $router->run("cron-events", 'get')->drawWithWrappers();
//        $this->assertEquals($expectedHtml, $html);
        $html = $router->call("lk/1/cron-events", 'get')->drawWithWrappers();
        $html = preg_replace('/fm\w+\s/si', '', $html);
        $this->assertEquals($expectedHtml, $html);
        $this->assertEmpty($router->call("lk/1/cron-events/2/active-triggers", 'get')->drawWithWrappers());


        /** @var RecordView $r */
        $r = $router->call("lk/1/cron-events/" . $id, 'get');

        $form = (new RecordForm)
            ->load($r->getPayload())
            ->setMethod('PUT')
            ->setAction("lk/1/cron-events/" . $id);

        $cl = $form->getFormClass();

        $expectedRowEditHtml = ''
            . '<form class="' . $cl . ' form pina-form" action="/put!lk/1/cron-events/' . $id . '" method="post">'
            . '<div class="card"><div class="card-body">'
            . $this->getEditFormInner($id)
            . '</div></div>'
            . '<div class="buttons"><button type="submit" class="btn btn-primary">Сохранить</button></div>'
            . '</form>';


        $this->assertEquals($expectedRowEditHtml, (string)$form);

        /** @var RecordView $view */
        $view = $router->call("lk/1/cron-events/" . $id, 'get');
        $form = new RecordForm();
        $view->getPayload()->getSchema()->fieldset(['payload'])->setStatic(false);
        $form->load($view->getPayload())
            ->setMethod("PUT")
            ->setAction("lk/1/cron-events/" . $id);

        $cl = $form->getFormClass();

        $expectedWrapHtml = ''
            . '<form class="' . $cl . ' form pina-form" action="/put!lk/1/cron-events/' . $id . '" method="post">'
            . '<div class="card"><div class="card-body">'
            . $this->getForcedEditFormInner($id)
            . '</div></div>'
            . '<div class="buttons"><button type="submit" class="btn btn-primary">Сохранить</button></div>'
            . '</form>';

        App::container()->set(FormStatic::class, FormInput::class);
        $this->assertEquals($expectedWrapHtml, (string)$form);
        App::container()->set(FormStatic::class, FormStatic::class);

        $r = $router->call("lk/1/cron-events/" . $id, 'get');
        $r->wrap(new \Pina\Controls\Wrapper('td'));
        $r->wrap(new \Pina\Controls\Components\TableRow);
        $r->wrap(new \Pina\Controls\Components\Table);
        $note = (new Paragraph)->setText('note');
        $form = (new Form)->setAction('/')->setMethod('delete');
        $form->append($note);
        $r->wrap($form);

        $removeButton = '<a class="pina-action btn btn-default" href="#" data-resource="lk/1/cron-events/'.$id.'" data-method="delete" data-params="">Удалить</a>';

        $expectedWrapHtml = '<form action="/delete!" method="post">'
            . '<p>note</p>'
            . '<table><tr><td>'
            . '<div class="row"><div class="col-lg-8">'
            . '<div><div class="card"><div class="card-body">'
            . $this->getStaticFormInner($id)
            . '</div></div></div>'
            . '<div class="buttons row"><div class="col-sm-4"></div><div class="col-sm-8 text-right">' . $removeButton .'</div></div>'
            . '</div></div>'
            . '</td></tr></table>'
            . '</form>';

        $this->assertEquals($expectedWrapHtml, (string)$r);

        $expectedWrapHtml = '<table><tr><td>'
            . '<div class="row"><div class="col-lg-8">'
            . '<div><div class="card"><div class="card-body">'
            . $this->getStaticFormInner($id)
            . '</div></div></div>'
            . '<div class="buttons row"><div class="col-sm-4"></div><div class="col-sm-8 text-right">' . $removeButton . '</div></div>'
            . '</div></div>'
            . '</td></tr></table>';

        $this->assertEquals($expectedWrapHtml, (string)$r->unwrap());

        $r = $router->call("lk/1/cron-events", 'delete');
        $class = new ReflectionClass($r);
        $prop = $class->getProperty('code');
        $prop->setAccessible(true);
        $this->assertEquals('400 Bad Request', $prop->getValue($r));

        $r = $router->call("lk/1/cron-events/" . $id, 'delete');
        $class = new ReflectionClass($r);
        $prop = $class->getProperty('code');
        $prop->setAccessible(true);
        $this->assertEquals('200 OK', $prop->getValue($r));
    }

    /**
     * @throws Exception
     */
    public function testHidden()
    {
        $schema = new Schema();
        $schema->add('mode', 'title', \Pina\Types\StringType::class)->setHidden();
        $form = new RecordForm();
        $form->load(new DataRecord(['mode' => 'test'], $schema));
        $r = $form->drawWithWrappers();

        $cl = $form->getFormClass();

        $expected = '<form class="' . $cl . ' form pina-form" action="" method="get">'
            . '<div class="card"><div class="card-body">'
            . '<input type="hidden" name="mode" value="test">'
            . '</div></div>'
            . '<div class="buttons"><button type="submit" class="btn btn-primary">Сохранить</button></div>'
            . '</form>';
        $this->assertEquals($expected, $r);
    }

    private function getStaticFormInner($id)
    {
        return '<div class="form-group"><label class="control-label">ID</label><div class="form-control-static">'.$id.'</div></div>'
            . '<div class="form-group"><label class="control-label">Handler</label><div class="form-control-static">order.paid</div></div>'
            . '<div class="form-group"><label class="control-label">Payload</label><div class="form-control-static">123</div></div>'
            . '<div class="form-group"><label class="control-label">Priority</label><div class="form-control-static">1</div></div>'
            . '<div class="form-group"><label class="control-label">Delay</label><div class="form-control-static">0</div></div>'
            . '<div class="form-group"><label class="control-label">Error</label><div class="form-control-static"></div></div>'
            . '<div class="form-group"><label class="control-label">Worker ID</label><div class="form-control-static">-</div></div>'
            . '<div class="form-group"><label class="control-label">Created at</label><div class="form-control-static">2020-01-02 03:04:05</div></div>'
            . '<div class="form-group"><label class="control-label">Scheduled at</label><div class="form-control-static">2020-01-02 03:04:05</div></div>'
            . '<div class="form-group"><label class="control-label">Started at</label><div class="form-control-static"></div></div>';
    }

    private function getEditFormInner($id)
    {
        return '<div class="form-group"><label class="control-label">ID *</label><div class="form-control-static">'.$id.'</div></div>'
            . '<div class="form-group"><label class="control-label">Handler *</label><div class="form-control-static">order.paid</div></div>'
            . '<div class="form-group"><label class="control-label">Payload</label><div class="form-control-static">123</div></div>'
            . '<div class="form-group"><label class="control-label">Priority</label><div class="form-control-static">1</div></div>'
            . '<div class="form-group"><label class="control-label">Delay</label><div class="form-control-static">0</div></div>'
            . '<div class="form-group"><label class="control-label">Error</label><div class="form-control-static"></div></div>'
            . '<div class="form-group"><label class="control-label">Worker ID</label><div class="form-control-static">-</div></div>'
            . '<div class="form-group"><label class="control-label">Created at</label><div class="form-control-static">2020-01-02 03:04:05</div></div>'
            . '<div class="form-group"><label class="control-label">Scheduled at</label><div class="form-control-static">2020-01-02 03:04:05</div></div>'
            . '<div class="form-group"><label class="control-label">Started at</label><div class="form-control-static"></div></div>';
    }
    private function getForcedEditFormInner($id)
    {
        return '<div class="form-group"><label class="control-label">ID *</label><input type="text" class="form-control" name="id" value="'.$id.'"></div>'
            . '<div class="form-group"><label class="control-label">Handler *</label><input type="text" class="form-control" name="handler" value="order.paid"></div>'
            . '<div class="form-group"><label class="control-label">Payload</label><textarea class="form-control" name="payload" rows="3">123</textarea></div>'
            . '<div class="form-group"><label class="control-label">Priority</label><input type="text" class="form-control" name="priority" value="1"></div>'
            . '<div class="form-group"><label class="control-label">Delay</label><input type="text" class="form-control" name="delay" value="0"></div>'
            . '<div class="form-group"><label class="control-label">Error</label><input type="text" class="form-control" name="error" value=""></div>'
            . '<div class="form-group"><label class="control-label">Worker ID</label><input type="text" class="form-control" name="worker_id" value="-"></div>'
            . '<div class="form-group"><label class="control-label">Created at</label><input type="text" class="form-control" name="created_at" value="2020-01-02 03:04:05"></div>'
            . '<div class="form-group"><label class="control-label">Scheduled at</label><input type="text" class="form-control" name="scheduled_at" value="2020-01-02 03:04:05"></div>'
            . '<div class="form-group"><label class="control-label">Started at</label><input type="text" class="form-control" name="started_at" value=""></div>';
    }

}
