<?php


namespace Pina\Controls\Record;


use Pina\App;
use Pina\Controls\Components\Checkbox;
use Pina\Controls\Components\TableCheckAllHeaderCell;
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
        if ($field->isStatic() == false) {
            $type = $field->getType();
            $t = App::make($type);
            if ($t instanceof CheckedEnabledType) {
                $checkAllHeaderCell = TableCheckAllHeaderCell::make();
                $checkAllHeaderCell->setRootName($this->name);
                $checkAllHeaderCell->setFieldName($field->getName());
                return $checkAllHeaderCell;
            }
        }
        return parent::makeTableHeaderCell($field);
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