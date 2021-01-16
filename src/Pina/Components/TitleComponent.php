<?php

namespace Pina\Components;

use Pina\Components\RecordData;
use Pina\Controls\RawHtml;

class TitleComponent extends RecordData
{

    public function build()
    {
        $h = new RawHtml();
        $data = $this->getData();
        $h->setText($data['title']);

        $this->append($h);
    }

}
