<?php

namespace Pina;

use Pina\Components\Schema;
use Pina\Components\ListData;
use Pina\Components\RecordData;
use Pina\Components\TableComponent;
use Pina\Components\ListComponent;
use Pina\Components\RecordViewComponent;
use Pina\Components\LocationComponent;
use Pina\Http\Endpoint;

class CronEventEndpoint extends Endpoint
{

    protected $schema = null;
    protected $parent = null;

    public function __construct()
    {
        $this->schema = new Schema();
        $this->schema->add('id', 'ID', 'immutable');
        $this->schema->add('event', 'Event', 'string');
        $this->schema->add('data', 'Data', 'text');
        $this->schema->add('priority', 'Priority', 'int');
        $this->schema->add('created', 'Created at', 'date');
        
        parent::__construct();
        
//        $this->parent = $parent;
    }

    public function index()
    {
        $data = CronEventGateway::instance()->get();

        return (new TableComponent())
                ->load($data, $this->schema)
                ->setMeta('title', 'Events')
                ->setMeta('breadcrumb', $this->getBreadcrumb())
        ;
    }

    public function show($id)
    {
        $data = CronEventGateway::instance()->find($id);
        
        return (new RecordViewComponent())
                ->load($data, $this->schema)
                ->setMeta('title', $data['event'])
                ->setMeta('breadcrumb', $this->getBreadcrumb()->push(LocationComponent::make('Component ' . $data['event'])))
        ;
    }
    
    public function destroy($id)
    {
        if (is_null($id)) {
            return \Pina\Response::badRequest();
        }
        CronEventGateway::instance()->whereId($id)->delete();
        return \Pina\Response::ok();
    }

    public function getBreadcrumb()
    {
//        $list = $this->parent->getBreadcrumbs();
        $list = new ListData();
        $list->push(LocationComponent::make('Home', '/'));
        $list->push(LocationComponent::make('Events', '/events'));
        return $list;
    }
    
    public function indexActiveTriggers($params)
    {
        return (new ListComponent());
    }

}
