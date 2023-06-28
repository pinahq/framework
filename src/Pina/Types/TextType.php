<?php

namespace Pina\Types;

use Pina\App;
use Pina\Controls\FormTextarea;

class TextType extends StringType
{

    public function getSize(): int
    {
        return 1024 * 1024;
    }

    public function isNullable(): bool
    {
        return true;
    }

    public function isFiltrable(): bool
    {
        return false;
    }

    public function getDefault()
    {
        return null;
    }

    public function getSQLType(): string
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
