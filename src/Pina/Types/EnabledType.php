<?php


namespace Pina\Types;


use function Pina\__;

class EnabledType extends EnumType
{
    public function __construct()
    {
        $this->variants = [
            ['id' => 'Y', 'title' => __('Включен')],
            ['id' => 'N', 'title' => __('Выключен')],
        ];
    }

    public function getDefault()
    {
        return 'Y';
    }
}