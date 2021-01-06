<?php

namespace Pina\Layouts;

use Pina\TemplateLayoutContent;

class SmartyLayout extends DefaultLayout
{

    public function draw()
    {
        $content = new TemplateLayoutContent;
        $content->drawLayout($this->compile());
        return $content->fetch();
    }

}
