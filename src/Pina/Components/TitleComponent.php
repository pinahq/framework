<?php

namespace Pina\Components;

use Pina\Components\RecordData;
use Pina\Controls\RawHtml;

class TitleComponent extends RecordData
{

    public function build()
    {
        $h = new RawHtml();
        $h->setText($this->data['title']);

        $this->append($h);
    }

}
