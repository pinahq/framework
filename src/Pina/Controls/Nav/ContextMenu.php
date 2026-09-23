<?php

namespace Pina\Controls\Nav;

use Pina\Layouts\EmptyLayout;

class ContextMenu extends Nav
{

    public function makeLayout()
    {
        return EmptyLayout::make();
    }

}