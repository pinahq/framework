<?php


namespace Pina\Types;


use Pina\App;
use Pina\SQL;
use Pina\TableDataGateway;

use function Pina\__;

class Relation extends DirectoryType
{
    /**
     * @var TableDataGateway
     */
    protected $relationTable;
    /**
     * @var string
     */
    protected $relationField = '';
    /**
     * @var string
     */
    protected $directoryField = '';
    /**
     * @var TableDataGateway
     */
    protected $directoryTable;

    public function __construct(
        TableDataGateway $relationTable,
        $relationField,
        $directoryField,
        TableDataGateway $directoryTable
    ) {
        $this->relationTable = $relationTable;
        $this->relationField = $relationField;
        $this->directoryField = $directoryField;
        $this->directoryTable = $directoryTable;
    }

    protected function makeDirectoryQuery(): TableDataGateway
    {
        return clone $this->directoryTable;
    }

    protected function makeRelationQuery(): TableDataGateway
    {
        return clone $this->relationTable;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getVariants()
    {
        return $this->makeDirectoryQuery()->selectId()->selectTitle()->get();
    }

    protected function makeSelect()
    {
        $input = parent::makeSelect();
        $input->setMultiple(true);
        return $input;
    }

    /**
     * @param mixed $value
     * @return string
     * @throws \Exception
     */
    public function format($value): string
    {
        $query = $this->makeDirectoryQuery()->whereId($value)->selectTitle();
        return implode(', ', $query->column('title'));
    }

    public function play($value): string
    {
        $query = $this->makeDirectoryQuery()->whereId($value)->selectId();
        $list = $query->get();

        $types = array_combine($this->relationTable->getSchema()->getFieldNames(), $this->relationTable->getSchema()->getFieldTypes());
        $type = $types[$this->directoryField];

        $r = [];
        foreach ($list as $item) {
            $r[] = App::type($type)->play($item['id']);
        }
        return implode(', ', $r);
    }

    /**
     * @param mixed $value
     * @param bool $isMandatory
     * @return mixed
     * @throws \Exception
     */
    public function normalize($value, $isMandatory)
    {
        $originalCount = is_array($value) ? count(array_unique($value)) : 0;

        if ($isMandatory && $originalCount == 0) {
            throw new ValidateException(__("Выберите значение"));
        }
        $count = $this->makeDirectoryQuery()->whereId($value)->count();
        if ($count <> $originalCount) {
            throw new ValidateException(__("Выбрано несуществующее значение"));
        }

        return $value;
    }

    public function getData($id)
    {
        return $this->makeRelationQuery()->whereBy($this->relationField, $id)->column($this->directoryField);
    }

    public function setData($id, $value)
    {
        $this->makeRelationQuery()
            ->whereBy($this->relationField, $id)
            ->delete();

        $toInsert = [];
        if (!is_array($value)) {
            return;
        }
        foreach ($value as $item) {
            $toInsert[] = [
                $this->relationField => $id,
                $this->directoryField => $item,
            ];
        }

        $this->makeRelationQuery()->insert($toInsert);
    }

    public function filter(TableDataGateway $query, $key, $value): void
    {
        if (empty($value)) {
            return;
        }

        $fields = is_array($key) ? $key : [$key];

        $subquery = SQL::subquery(
            $this->makeRelationQuery()
                ->calculate('DISTINCT ' . $this->relationField, $this->relationField)
                ->whereBy($this->directoryField, $value)
        );

        $query->innerJoin(
        //TODO: вычислять наименование PK
            $subquery->alias('filter_' . implode('_', $fields))->on($this->relationField, 'id')
        );
    }

    public function getSize(): int
    {
        return 0;
    }

    public function getSQLType(): string
    {
        return '';
    }

}