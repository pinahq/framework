<?php

namespace Pina;

use Pina\Components\Schema;
use Pina\Components\ListData;
use Pina\Components\TableComponent;
use Pina\Components\RecordViewComponent;

class CronEventEndpoint extends EndpointController
{

    protected $schema = null;

    public function __construct()
    {
        $this->schema = new Schema();
        $this->schema->add('id', '#');
        $this->schema->add('event', 'Событие');
        $this->schema->add('created', 'Дата создания');
    }

    public function index($params)
    {
        $data = CronEventGateway::instance()->get();

        return (new TableComponent())
                ->load($data, $this->schema)
                ->meta('title', 'Events')
                ->meta('breadcrumb', $this->getBreadcrumb())
        ;
    }

    public function show($params)
    {
        $data = CronEventGateway::instance()->find($params['id']);

        return (new RecordViewComponent())
                ->load($data, $this->schema)
                ->meta('title', $data['event'])
                ->meta('breadcrumb', $this->getBreadcrumb()->add(['', 'Component ' . $data['event']]))
        ;
    }

    public function getBreadcrumb()
    {
        return new ListData([
            ['url' => '/', 'title' => 'Home'],
            ['url' => '/events', 'title' => 'Events'],
        ]);
    }

}
