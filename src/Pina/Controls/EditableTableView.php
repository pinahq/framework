<?php


namespace Pina\Controls;


use Pina\App;
use Pina\Data\DataRecord;

class EditableTableView extends TableView
{
    protected $name = '';

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return RecordRow
     */
    protected function makeRow(DataRecord $record)
    {
        /** @var EditableRecordRow $row */
        $row = App::make(EditableRecordRow::class);
        $row->load($record);
        $row->setName($this->name);
        return $row;
    }

}