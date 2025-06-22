<?php

namespace Pina\Events\Cron;

use Pina\App;
use Pina\Composers\CollectionComposer;
use Pina\Controls\UnorderedList;
use Pina\Controls\RecordView;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Http\Request;
use Pina\Http\RichEndpoint;
use Pina\Processors\CollectionItemLinkProcessor;
use Pina\Response;
use function Pina\__;

class CronEventEndpoint extends RichEndpoint
{
    public function title()
    {
        return __('События');
    }

    public function index()
    {
        $this->makeCollectionComposer($this->title())->index($this->location());
        $query = CronEventGateway::instance();

        $data = $query->get();
        $schema = $query->getQuerySchema();
        $schema->pushHtmlProcessor(new CollectionItemLinkProcessor($schema, $this->location()));

        return $this->makeTableView(new DataTable($data, $schema));
    }

    /**
     * @param $id
     * @return RecordView
     * @throws \Exception
     */
    public function show($id)
    {
        $data = CronEventGateway::instance()->findOrFail($id);

        $schema = CronEventGateway::instance()->getSchema();
        $schema->forgetField('id');

        $record = new DataRecord($data, $schema);

        /** @var RecordView $view */
        $view = App::make(RecordView::class);
        $view->load($record);

        $this->makeCollectionComposer($this->title())->show($this->location(), $record);

        if (empty($data['worker_id'])) {
            $view->append($this->makeActionButton(__('Удалить'), $this->location()->resource('@'), 'delete'));
        }

        return $view;
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
        CronEventGateway::instance()->whereId($id)->whereNull('worker_id')->delete();
        return Response::ok()->contentLocation($this->base()->link('@'));
    }

    public function indexActiveTriggers()
    {
        return (new UnorderedList());
    }

}
