<?php

namespace Pina\Types;

use Pina\Data\Schema;

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

    public function addTimestampsToSchema(Schema $schema)
    {
        $statuses = $this->getVariants();
        foreach ($statuses as $status) {
            $name = $this->normalizeFieldName($status['id']);
            $schema->add($name . '_at', $status['title'], TimestampType::class)
                ->setStatic()->setHidden()->setNullable()->setWidth(6);
        }
    }

    protected function normalizeFieldName($name)
    {
        return str_replace('-', '_', $name);
    }

    public function getIndependentTimestampTriggers($field, $ignoredStatuses)
    {
        $statuses = array_column($this->getVariants(), 'id');
        $statuses = array_diff($statuses, $ignoredStatuses);

        $insertTrigger = '';
        $updateTrigger = '';

        foreach ($statuses as $status) {
            list($insertTriggerPart, $updateTriggerPart) = $this->getTimestampTriggerPart($field, [$status]);
            $insertTrigger .= $insertTriggerPart;
            $updateTrigger .= $updateTriggerPart;
        }

        return [$insertTrigger, $updateTrigger];
    }

    public function getChainTimestampTriggers($field, $ignoredStatuses)
    {
        $statuses = array_column($this->getVariants(), 'id');

        $insertTrigger = '';
        $updateTrigger = '';

        if (array_intersect($ignoredStatuses, $statuses)) {
            list($insertTriggerPart, $updateTriggerPart) = $this->getTimestampTriggerPart($field, $ignoredStatuses);
            $insertTrigger .= $insertTriggerPart;
            $updateTrigger .= $updateTriggerPart;
            $statuses = array_diff($statuses, $ignoredStatuses);
            $statuses = array_values($statuses);
        }

        while (1) {
            if (empty($statuses)) {
                break;
            }

            list($insertTriggerPart, $updateTriggerPart) = $this->getTimestampTriggerPart($field, $statuses);
            $insertTrigger .= $insertTriggerPart;
            $updateTrigger .= $updateTriggerPart;

            array_shift($statuses);
        }

        return [$insertTrigger, $updateTrigger];
    }

    protected function getTimestampTriggerPart($field, $statuses)
    {
        $statusesCondition = "('".implode("','", $statuses)."')";
        $insertTriggerCondition = "NEW.$field IN $statusesCondition";
        $updateTriggerCondition = "NEW.$field <> OLD.$field AND NEW.$field IN $statusesCondition AND OLD.$field NOT IN $statusesCondition";
        $firstStatus = $this->normalizeFieldName($statuses[0]);
        $insertTrigger = "IF ($insertTriggerCondition) THEN "
            . " SET NEW.".$firstStatus."_at=NOW();"
            . " END IF;";
        $updateTrigger = "IF ($updateTriggerCondition) THEN "
            . " SET NEW.".$firstStatus."_at=NOW();"
            . " END IF;";

        return [$insertTrigger, $updateTrigger];
    }

}
