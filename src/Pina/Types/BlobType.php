<?php


namespace Pina\Types;


class BlobType extends TextType
{

    public function getSQLType(): string
    {
        $size = $this->getSize();
        if ($size <= 65535) {
            return "blob";
        }
        if ($size <= 16777215) {
            return "mediumblob";
        }
        return "longblob";
    }

}