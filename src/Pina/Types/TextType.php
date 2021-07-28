<?php

namespace Pina\Types;

use Pina\App;
use Pina\Controls\FormTextarea;

class TextType extends StringType
{

    public function getSize()
    {
        return 1024 * 1024;
    }

    public function isNullable()
    {
        return true;
    }

    public function getDefault()
    {
        return null;
    }

    public function getSQLType()
    {
        $size = $this->getSize();
        if ($size <= 65535) {
            return "text";
        }
        if ($size <= 16777215) {
            return "mediumtext";
        }
        return "longtext";
    }

    protected function makeInput()
    {
        return App::make(FormTextarea::class);
    }

}
