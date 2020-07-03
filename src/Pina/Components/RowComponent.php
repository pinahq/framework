<?php

namespace Pina\Components;

use Pina\Html;

class RowComponent extends RecordData //implements ComponentInterface
{

    protected $tag = 'td';
    
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
