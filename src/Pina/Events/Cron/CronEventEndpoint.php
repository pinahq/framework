<?php

namespace Pina\Events\Cron;

use Pina\Controls\UnorderedList;
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

    public function index()
    {
        $query = CronEventGateway::instance()
            ->selectAll()
            ->leftJoin(
                CronEventWorkerGateway::instance()->on('id', 'id')
                    ->select('worker_id')
            );

        $data = $query->get();
        $schema = $query->getQuerySchema();

        return (new TableView)->load(new DataTable($data, $schema));
    }

    /**
     * @param $id
     * @return RecordView
     * @throws \Exception
     */
    public function show($id)
    {
        $data = CronEventGateway::instance()->find($id);

        $schema = CronEventGateway::instance()->getSchema();
        $schema->forgetField('id');

        return (new RecordView)->load(new DataRecord($data, $schema));
    }

    /**
     * @param $id
     * @return Response
     * @throws \Exception
     */
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
