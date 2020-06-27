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

        return (new TableComponent())->load($data, $this->schema);
    }
    
    public function show($params)
    {
        $data = CronEventGateway::instance()->find($params['id']);

        return (new RecordViewComponent())->load($data, $this->schema);
    }

}
