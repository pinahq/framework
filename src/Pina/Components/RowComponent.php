<?php

namespace Pina\Components;

use Pina\Html;

class RowComponent extends RecordData //implements ComponentInterface
{

    protected $tag = 'td';
    /**
     * 
     * @param \Pina\RecordData $record
     * @return $this
     */
    public function basedOn(RecordData $record)
    {
        return $this->load($record->data, $record->schema);
    }
    
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    public function draw()
    {
        $data = $this->schema->makeFlatLine($this->data);
        $r = '';
        foreach ($data as $k => $v) {
            $r .= Html::tag($this->tag, $v);
        }
        return Html::tag('tr', $r);
    }

}
