<?php

namespace Pina\Layouts;

use Pina\TemplateLayoutContent;

class SmartyLayout extends DefaultLayout
{

    public function draw()
    {
        $content = new TemplateLayoutContent;
        $content->drawLayout($this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter());
        return $content->fetch();
    }

}
