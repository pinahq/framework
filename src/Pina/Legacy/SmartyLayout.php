<?php

namespace Pina\Legacy;

use Pina\Layouts\DefaultLayout;

class SmartyLayout extends DefaultLayout
{

    public function draw()
    {
        $content = new TemplateLayoutContent;
        $content->drawLayout($this->drawInnerBefore() . $this->drawInner() . $this->drawInnerAfter());
        return $content->fetch();
    }

}
