<?php

namespace Pina\Components;

use Pina\Controls\RawHtml;

class HeaderComponent extends RecordData
{

    public function build()
    {
        $h = new RawHtml();
        $h->setText('<h1>' . $this->data['title'] . '</h1>');
        
        $this->append($h);
    }

}
