<?php

namespace Pina\Queue;

use Pina\Command;
use Pina\Controls\Components\ButtonRow;
use Pina\Controls\Components\UnorderedList;
use Pina\Data\DataCollection;
use Pina\Data\DataRecord;
use Pina\Data\QueryDataCollection;
use Pina\Http\DelegatedCollectionEndpoint;
use Pina\Response;
use function Pina\__;

class QueueEndpoint extends DelegatedCollectionEndpoint
{
    protected function getCollectionTitle(): string
    {
        return __('Очередь');
    }

    protected function makeDataCollection(): DataCollection
    {
        return new QueryDataCollection(QueueGateway::instance());
    }

    protected function makeViewButtonRow(DataRecord $record): ButtonRow
    {
        $row = parent::makeViewButtonRow($record);

        if (!$record->getValue('worker_id')) {
            $row->append($this->makeActionButton(__('Удалить'), $this->location()->resource('@'), 'delete'));
        }

        if (!$record->getValue('worker_id') && $record->getValue('delay') > 0) {
            $row->append($this->makeActionButton(__('В начало очереди'), $this->location()->resource('@'), 'put'));
        }

        return $row;
    }

    public function store()
    {
        $normalized = $this->makeDataCollection()->getCreationSchema()->normalize($this->request()->all());
        if (!class_exists($normalized['handler'])) {
            return Response::badRequest(__('Класс не существует'), 'handler');
        }
        if (!is_subclass_of($normalized['handler'], Command::class)) {
            return Response::badRequest(__('Класс не является командой'), 'handler');
        }
        call_user_func([$normalized['handler'], 'enqueue'], $normalized['payload'], $normalized['priority']);
        return Response::ok()->contentLocation($this->base()->link('@'));
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
