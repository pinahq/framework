<?php


namespace Pina\Types;


use Pina\TableDataGateway;

use function Pina\__;

abstract class QueryDirectoryType extends DirectoryType
{
    abstract protected function makeQuery(): TableDataGateway;

    /**
     * @return array
     * @throws \Exception
     */
    public function getVariants()
    {
        return $this->makeQuery()->selectId()->selectTitle()->get();
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function format($value)
    {
        return $this->makeQuery()->whereId($value)->selectTitle()->value('title');
    }

    public function normalize($value, $isMandatory)
    {
        if (!$this->makeQuery()->whereId($value)->exists()) {
            throw new ValidateException(__("Выберите значение"));
        }

        return $value;
    }

    public function getSize()
    {
        return 11;
    }

    public function getSQLType()
    {
        return "int(" . $this->getSize() . ")";
    }
}