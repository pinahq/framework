<?php


namespace Pina\Types;

use function Pina\__;

class BooleanType extends EnumType
{

    public function __construct()
    {
        $this->variants = [
            ['id' => 'Y', 'title' => __('Yes')],
            ['id' => 'N', 'title' => __('No')],
        ];
    }

    public function isNullable()
    {
        return false;
    }

    public function getDefault()
    {
        return 'N';
    }

}