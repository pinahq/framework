<?php


namespace Pina\Controls;


use Pina\App;
use Pina\Data\DataRecord;
use Pina\Data\Field;
use Pina\Types\CheckedEnabledType;

class EditableTableView extends TableView
{
    protected $name = '';

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    protected function makeTableHeaderCell(Field $field)
    {
        $cell = parent::makeTableHeaderCell($field);
        if ($field->isStatic() == false) {
            $type = $field->getType();
            $t = App::make($type);
            if ($t instanceof CheckedEnabledType) {
                $id = uniqid('ch');
                /** @var Checkbox $checkbox */
                $checkbox = App::make(Checkbox::class);
                $checkbox->setId($id);
                $checkbox->setName('checkbox-all');
                $checkbox->setValue('Y');
                $this->generateCheckAll($id, $field->getKey());
                $cell->append($checkbox);
            }
        }
        return $cell;
    }

    protected function generateCheckAll($id, $name)
    {
        $pattern = '/' . $this->name . '\[\d+\]\[' . $name . '\]/';
        App::assets()->addScriptContent(
            "<script>document.getElementById('$id').addEventListener('click', function() {let es = this.parentNode.parentNode.parentNode.querySelectorAll('input[type=checkbox]'); for (let i=0;i<es.length;i++) {if (es[i].name.match($pattern)){es[i].checked=this.checked;}}});</script>"
        );
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