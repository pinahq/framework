<?php


namespace Pina\Types;


use Pina\TableDataGateway;

use function Pina\__;

abstract class QueryDirectoryType extends DirectoryType
{
    protected $cacheSeconds = 0;

    abstract protected function makeQuery(): TableDataGateway;

    /**
     * @return array
     * @throws \Exception
     */
    public function getVariants()
    {
        return $this->makeQuery()->selectId()->selectTitle()->get($this->cacheSeconds);
    }

    /**
     * @param mixed $value
     * @return string
     * @throws \Exception
     */
    public function format($value): string
    {
        if (empty($value)) {
            return '';
        }
        return implode(', ', $this->makeQuery()->whereId($value)->selectTitle()->column('title', null, $this->cacheSeconds));
    }

    /**
     * @param mixed $value
     * @param bool $isMandatory
     * @return mixed
     * @throws \Exception
     */
    public function normalize($value, $isMandatory)
    {
        if (!$this->makeQuery()->whereId($value)->exists()) {
            throw new ValidateException(__("Выберите значение"));
        }

        return $value;
    }

    public function getSize(): int
    {
        return 11;
    }

    public function getSQLType(): string
    {
        return "int(" . $this->getSize() . ")";
    }
}