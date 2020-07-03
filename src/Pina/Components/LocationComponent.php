<?php

namespace Pina\Components;

use Pina\Html;

class LocationComponent extends RecordData //implements ComponentInterface
{
    
    public static function make($title, $url = '')
    {
        $l = new LocationComponent();
        $l->data['title'] = $title;
        $l->data['url'] = $url;
        
        $l->schema = new Schema();
        $l->schema->add('url', 'Url');
        $l->schema->add('title', 'Title');
        
        return $l;
    }

    public function draw()
    {
        return Html::tag('tr', $r);
    }

}
