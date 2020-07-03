<?php

namespace Pina;

use Pina\Components\Schema;
use Pina\Components\ListData;
use Pina\Components\RecordData;
use Pina\Components\TableComponent;
use Pina\Components\ListComponent;
use Pina\Components\RecordViewComponent;
use Pina\Components\LocationComponent;

class CronEventEndpoint extends EndpointController
{

    protected $schema = null;
    protected $parent = null;

    public function __construct()
    {
        $this->schema = new Schema();
        $this->schema->add('id', 'ID', 'immutable');
        $this->schema->add('event', 'Event', 'string');
        $this->schema->add('created', 'Created at', 'date');
        
//        $this->parent = $parent;
    }

    public function index($params)
    {
        $data = CronEventGateway::instance()->get();

        return (new TableComponent())
                ->load($data, $this->schema)
                ->setMeta('title', 'Events')
                ->setMeta('breadcrumb', $this->getBreadcrumb())
        ;
    }

    public function show($params)
    {
        $data = CronEventGateway::instance()->find($params['id']);

        return (new RecordViewComponent())
                ->load($data, $this->schema)
                ->setMeta('title', $data['event'])
                ->setMeta('breadcrumb', $this->getBreadcrumb()->add(LocationComponent::make('Component ' . $data['event'])))
        ;
    }

    public function getBreadcrumb()
    {
//        $list = $this->parent->getBreadcrumbs();
        $list = new ListData();
        $list->add(LocationComponent::make('Home', '/'));
        $list->add(LocationComponent::make('Events', '/events'));
        return $list;
    }
    
    public function indexActiveTriggers($params)
    {
        return (new ListComponent());
    }

}
