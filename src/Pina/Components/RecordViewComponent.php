<?php

namespace Pina\Components;

use Pina\Controls\FormStatic;

class RecordViewComponent extends RecordData //implements ComponentInterface
{

    public function build()
    {
        $fields = $this->schema->getFields();
        $titles = $this->schema->getTitles();

        foreach ($fields as $k => $field) {
            $title = $titles[$k] ? $titles[$k] : '';
            $value = $this->data[$field] ? $this->data[$field] : '';
            $static = FormStatic::instance()->setTitle($title)->setValue($value);
            $this->append($static);
        }
        
    }

}
