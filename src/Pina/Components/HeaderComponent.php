<?php

namespace Pina\Components;

use Pina\Controls\RawHtml;

class HeaderComponent extends RecordData
{

    public function build()
    {
        $h = new RawHtml();

        $data = $this->getTextData();
        $h->setText('<h1>' . $data['title'] . '</h1>');

        $this->append($h);
    }

}
