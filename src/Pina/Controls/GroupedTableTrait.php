<?php


namespace Pina\Controls;

use Pina\App;
use Pina\Data\DataRecord;
use Pina\Data\DataTable;

trait GroupedTableTrait
{
    protected $groupFieldName = '';

    /**
     * @var DataTable
     */
    protected $dataTable;

    public function setGroupFieldName($fieldName)
    {
        if (!$this->dataTable->getSchema()->has($fieldName)) {
            return $this;
        }

        $this->groupFieldName = $fieldName;
        $this->dataTable->getSchema()->forgetField($fieldName);
        return $this;
    }

    /**
     * @throws \Exception
     */
    protected function drawInner()
    {
        /** @var Table $table */
        $table = $this->makeTable();
        $table->append($this->buildHeader());

        /** @var \Pina\Data\DataRecord $oldRecord */
        $oldRecord = null;
        foreach ($this->dataTable as $record) {
            if (is_null($oldRecord) || $record->getMeta($this->groupFieldName) != $oldRecord->getMeta($this->groupFieldName)) {
                $table->append($this->makeGroupHeader($record->getMeta($this->groupFieldName)));
            }

            /** @var DataRecord $record */
            $table->append($this->makeRow($record));

            $oldRecord = $record;
        }
        return $table;
    }

    /**
     * @param $title
     * @return Control
     * @throws \Exception
     */
    protected function makeGroupHeader($title): Control
    {
        $count = count($this->dataTable->getSchema()->getFieldTitles());
        /** @var GroupedTableRow $row */
        $row = App::make(GroupedTableRow::class);
        $row->load($title, $count);
        return $row;
    }

}