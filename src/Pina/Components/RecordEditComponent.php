<?php

namespace Pina\Components;

use Pina\Html;

class RecordEditComponent extends RecordData //implements ComponentInterface
{

    public function draw()
    {
        $fields = $this->schema->getFields();
        $titles = $this->schema->getTitles();

        $r = '';
        foreach ($fields as $field) {
            $title = $this->schema->getTitle($field);
            $type = $this->schema->getType($field);
            $value = $this->data[$field] ? $this->data[$field] : '';
            $r .= Html::tag('label', $title);
            $r .= Html::tag('input', '', ['value' => $value]);
        }
        
        $method = 'PUT';
        $r .= \Pina\CSRF::formField($method);

        return Html::tag('form', $r, ['action' => $this->getMeta('location'), 'method' => $method, 'class' => 'form pina-form']);
    }

}
