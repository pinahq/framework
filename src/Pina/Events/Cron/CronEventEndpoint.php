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
        parent::__construct();
    }

    public function index()
    {
        $data = CronEventGateway::instance()->get();

        $schema = CronEventGateway::instance()->getSchema();

        return (new TableView)->load(new DataTable($data, $schema));
    }

    public function show($id)
    {
        $data = CronEventGateway::instance()->find($id);

        $schema = CronEventGateway::instance()->getSchema();
        $schema->forgetField('id');

        return (new RecordView)->load(new DataRecord($data, $schema));
    }

    public function destroy($id)
    {
        if (is_null($id)) {
            return Response::badRequest();
        }
        CronEventGateway::instance()->whereId($id)->delete();
        return Response::ok();
    }

    public function indexActiveTriggers()
    {
        return (new UnorderedList());
    }

}
