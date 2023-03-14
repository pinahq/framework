<?php


namespace Pina\Types;


use Pina\Controls\FormControl;
use Pina\Data\Field;
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

    public function __construct(TableDataGateway $relationTable, $relationField, $directoryField, TableDataGateway $directoryTable)
    {
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

    public function makeControl(Field $field, $value): FormControl
    {
        $control = parent::makeControl($field, $value);
        $control->setMultiple(true);
        return $control;
    }

    /**
     * @param mixed $value
     * @return string
     * @throws \Exception
     */
    public function format($value)
    {
        $query = $this->makeDirectoryQuery()->whereId($value)->selectTitle();
        return implode(', ', $query->column('title'));
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

    public function filter(TableDataGateway $query, string $key, $value): void
    {
        if (empty($value)) {
            return;
        }

        $subquery = SQL::subquery(
            $this->makeRelationQuery()
                ->calculate('DISTINCT ' . $this->relationField, $this->relationField)
                ->whereBy($this->directoryField, $value)
        );

        $query->innerJoin(
        //TODO: вычислять наименование PK
            $subquery->alias('filter_' . $key)->on($this->relationField, 'id')
        );
    }

    public function getSize()
    {
        return 0;
    }

    public function getSQLType()
    {
        return '';
    }

}