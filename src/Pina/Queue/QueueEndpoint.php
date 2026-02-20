<?php

namespace Pina\Queue;

use Pina\App;
use Pina\Controls\RecordView;
use Pina\Controls\UnorderedList;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Http\RichEndpoint;
use Pina\Processors\CollectionItemLinkProcessor;
use Pina\Response;
use function Pina\__;

class QueueEndpoint extends RichEndpoint
{
    public function title()
    {
        return __('Очередь');
    }

    public function index()
    {
        $this->makeCollectionComposer($this->title())->index($this->location());
        $query = QueueGateway::instance();

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
        $data = QueueGateway::instance()->findOrFail($id);

        $schema = QueueGateway::instance()->getSchema();
        $schema->forgetField('id');

        $record = new DataRecord($data, $schema);

        /** @var RecordView $view */
        $view = App::make(RecordView::class);
        $view->load($record);

        $this->makeCollectionComposer($this->title())->show($this->location(), $record);

        if (empty($data['worker_id'])) {
            $view->append($this->makeActionButton(__('Удалить'), $this->location()->resource('@'), 'delete'));
        }

        if (empty($data['worker_id']) && !empty($data['delay'])) {
            $view->append($this->makeActionButton(__('В начало очереди'), $this->location()->resource('@'), 'put'));
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
        QueueGateway::instance()->whereId($id)->whereNull('worker_id')->delete();
        return Response::ok()->contentLocation($this->base()->link('@'));
    }

    public function update($id)
    {
        if (is_null($id)) {
            return Response::badRequest();
        }
        QueueGateway::instance()->whereId($id)->whereNull('worker_id')->whereNotBy('delay', 0)->pullToHead();
        return Response::ok()->contentLocation($this->location()->link('@'));
    }

    public function indexActiveTriggers()
    {
        return (new UnorderedList());
    }

}
