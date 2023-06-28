<?php

namespace Pina\Types;

use function Pina\__;

class EnumType extends DirectoryType
{

    /**
     * @var array $variants
     */
    protected $variants;

    public function setVariants($variants)
    {
        $this->variants = $variants;
        return $this;
    }

    public function format($value): string
    {
        foreach ($this->variants as $v) {
            if ($v['id'] == $value) {
                return isset($v['title']) ? $v['title'] : '';
            }
        }

        return $value ?? '';
    }

    public function getVariants()
    {
        return $this->variants;
    }

    public function normalize($value, $isMandatory)
    {
        $ids = array_column($this->variants, 'id');
        if (!in_array($value, $ids)) {
            throw new ValidateException(__("Выберите значение"));
        }

        return $value;
    }

    public function getSQLType(): string
    {
        $variants = array_column($this->variants, 'id');
        return "enum('" . implode("','", $variants) . "')";
    }

}
