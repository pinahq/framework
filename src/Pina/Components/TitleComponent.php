<?php

namespace Pina\Components;

use Pina\Controls\RawHtml;

class TitleComponent extends RecordData
{

    public function build()
    {
        $h = new RawHtml();
        $data = $this->getTextData();
        $h->setText($data['title']);

        $this->append($h);
    }

}
