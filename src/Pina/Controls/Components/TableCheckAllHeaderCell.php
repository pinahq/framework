<?php

namespace Pina\Controls\Components;

use Pina\App;

class TableCheckAllHeaderCell extends TableHeaderCell
{

    protected $fieldName = '';
    protected $rootName = '';

    public function setRootName($rootName)
    {
        $this->rootName = $rootName;
    }

    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    protected function drawContent(): string
    {
        $id = uniqid('ch');
        /** @var Checkbox $checkbox */
        $checkbox = App::make(Checkbox::class);
        $checkbox->setId($id);
        $checkbox->setName('checkbox-all');
        $checkbox->setValue('Y');

        $this->generateCheckAll($id);

        return $checkbox . parent::drawContent();
    }

    protected function generateCheckAll($id)
    {
        $pattern = '/' . $this->rootName . '\[\d+\]\[' . $this->fieldName . '\]/';
        App::assets()->addScriptContent(
            "<script>document.getElementById('$id').addEventListener('click', function() {let es = this.parentNode.parentNode.parentNode.querySelectorAll('input[type=checkbox]'); for (let i=0;i<es.length;i++) {if (es[i].name.match($pattern)){es[i].checked=this.checked;}}});</script>"
        );
    }

}