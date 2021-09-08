<?php

namespace Pina\Events\Cron;

use Pina\Components\BreadcrumbComponent;
use Pina\Controls\UnorderedList;
use Pina\Data\Schema;
use Pina\Controls\RecordView;
use Pina\Controls\TableView;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Http\Endpoint;
use Pina\Response;

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
        $this->schema->add('created_at', 'Created at', 'date');
        
        parent::__construct();
        
//        $this->parent = $parent;
    }

    public function index()
    {
        $data = CronEventGateway::instance()->get();

        return (new TableView)
                ->load(new DataTable($data, $this->schema))
//                ->setMeta('title', 'Events')
//                ->setMeta('breadcrumb', $this->getBreadcrumb())
        ;
    }

    public function show($id)
    {
        $data = CronEventGateway::instance()->find($id);

        $schema = clone($this->schema);
        $schema->forgetField('id');

        return (new RecordView)
                ->load(new DataRecord($data, $schema))
//                ->setMeta('title', $data['event'])
//                ->setMeta('breadcrumb', $this->getBreadcrumb()->push(['title' => 'Event ' . $data['event'], 'link' => $this->location->link('@')]))
        ;
    }
    
    public function destroy($id)
    {
        if (is_null($id)) {
            return Response::badRequest();
        }
        CronEventGateway::instance()->whereId($id)->delete();
        return Response::ok();
    }

    public function getBreadcrumb()
    {
//        $list = $this->parent->getBreadcrumbs();
        $list = new BreadcrumbComponent();
        $list->push(['title' => 'Home', 'link' => '/']);
        $list->push(['title' => 'Events', 'link' => $this->base->link('@')]);
        return $list;
    }

    public function indexActiveTriggers()
    {
        return (new UnorderedList());
    }

}