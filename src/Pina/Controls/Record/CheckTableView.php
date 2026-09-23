<?php


namespace Pina\Controls\Record;


use Pina\App;
use Pina\Controls\Components\Checkbox;
use Pina\Controls\Wrapper;
use Pina\Data\DataRecord;

/**
 * Class CheckTableView
 * @package Pina\Controls
 *
 * Таблица с галками для массового обновления записей.
 * Расширяет типовую таблицу, добавляя в каждую строчку слева галку, и в заголовок галку "отметить все"
 * Требует для каждой записи мета-данные keys, в которых были бы перечислены идентификаторы строчки,
 * эти идентификаторы станут ключами в массиве имен чекбоксов bulk_edit_key[key1][key2]...[keyN]
 */
class CheckTableView extends TableView
{
    protected function buildHeader()
    {
        return parent::buildHeader()->prepend($this->makeCheckAll());
    }

    protected function makeRow(DataRecord $record)
    {
        return App::make(CheckRecordRow::class)->load($record);
    }

    public function makeCheckAll()
    {
        /** @var Checkbox $checkbox */
        $checkbox = App::make(Checkbox::class);
        $checkbox->setId('bulk-edit-checkbox-all');
        $checkbox->setName('bulk-edit-checkbox-all');
        $checkbox->setValue(1);
        $checkbox->wrap(new Wrapper("th"));
        return $checkbox;
    }


}