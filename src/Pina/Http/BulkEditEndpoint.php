<?php

namespace Pina\Http;


use Pina\App;
use Pina\Arr;
use Pina\Controls\RecordForm;
use Pina\Controls\TableView;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;
use Pina\Controls\CheckTableView;
use Pina\Processors\KeyMetaProcessor;
use Pina\Response;

/**
 * Class BulkEditEndpoint
 * @package Pina\Endpoints
 *
 * Базовый эндопоинт для экрана массового обновления данных.
 * Состоит из таблицы с галками, в которых можно отметить строчки и из формы для массового изменения
 * выбранных строчек.
 *
 * Опирается на схему данных, в которых основной ключ используется для галок, а форма для всех остальных полей кроме
 * основного ключа.
 *
 * При обработке формы формирует массив данных с ключами и значениями и обновляет данные одним SQL запросом
 */
abstract class BulkEditEndpoint extends FixedCollectionEndpoint
{

    /**
     * @return TableView
     */
    protected function makeCollectionView(DataTable $data)
    {
        /** @var CheckTableView $table */
        $table = App::make(CheckTableView::class);
        $table->load($data);

        $form = App::make(RecordForm::class);
        $form->setMethod('post')->setAction($this->location->link('@'));
        $form->load(new DataRecord([], $this->getBulkEditSchema()));

        $form->prepend($table);
        return $form;
    }

    public function getListSchema()
    {
        $schema = parent::getListSchema();
        $schema->pushMetaProcessor(new KeyMetaProcessor($schema->getPrimaryKey()));
        return $schema;
    }

    protected function getBulkEditSchema()
    {
        $schema = $this->getListSchema();
        $primaryKey = $schema->getPrimaryKey();
        foreach ($primaryKey as $key) {
            $schema->forgetField($key);
        }
        return $schema;
    }

    /**
     * @return Response
     * @throws \Exception
     */
    public function store()
    {
        $data = $this->request()->all();

        $schema = $this->getBulkEditSchema();
        $normalized = $schema->normalize($data);
        $bulkEditKeys = $data['bulk_edit_key'] ?? [];

        $primaryKey = $schema->getPrimaryKey();
        $keyData = Arr::mineKeyData($bulkEditKeys, $primaryKey);

        $data = [];
        foreach ($keyData as $keyLine) {
            $data[] = array_merge($keyLine, $normalized);
        }

        $this->makeQuery()->put($data);

        return Response::ok();
    }
}
