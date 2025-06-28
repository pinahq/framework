<?php


namespace Pina\Controls;


use Pina\App;
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
        $row = parent::makeRow($record);
        $row->prepend($this->makeCheck($record->getMeta('keys')));
        return $row;
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

    public function makeCheck($keys)
    {
        /** @var Checkbox $checkbox */
        $checkbox = App::make(Checkbox::class);
        $checkbox->setId('bulk_edit_key_' . implode('_', $keys));
        $checkbox->addClass('bulk-edit-checkbox');
        $keyPath = [];
        foreach ($keys as $item) {
            $keyPath[] = '[' . $item . ']';
        }
        $checkbox->setName('bulk_edit_key' . implode('', $keyPath));
        $checkbox->setValue(1);
        $checkbox->wrap(new Wrapper("td"));
        return $checkbox;
    }

}