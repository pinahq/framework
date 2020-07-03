<?php

namespace Pina\Components;

use Pina\Html;

class RecordViewComponent extends RecordData //implements ComponentInterface
{

    public function draw()
    {
        $fields = $this->schema->getFields();
        $titles = $this->schema->getTitles();
        
        $r = '';
        foreach ($fields as $k => $field) {
            $title = $titles[$k] ? $titles[$k] : '';
            $value = $this->data[$field] ? $this->data[$field] : '';
            $r .= Html::tag('label', $title);
            $r .= Html::tag('span', $value);
        }
        
        return $r;
    }

}
