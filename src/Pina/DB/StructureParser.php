<?php

namespace Pina\DB;

class StructureParser
{

    protected $meta = null;

    public function __construct($tableCondition)
    {
        $parser = new \iamcal\SQLParser();
        $this->meta = $parser->parse($tableCondition);
    }

    public function getIndexes()
    {
        if (empty($this->meta['child']['indexes'])) {
            return array();
        }

        $indexes = array();
        foreach ($this->meta['child']['indexes'] as $index) {
            if (
                    $index['type'] != 'FOREIGN'
            ) {
                if (empty($index['name']) && $index['type'] == 'PRIMARY') {
                    $index['name'] = 'PRIMARY';
                }
                if (empty($index['name'])) {
                    continue;
                }
                $indexObj = new Index(array_column($index['cols'], 'name'));
                $indexObj->setType($index['type']);
                $indexes[$index['name']] = $indexObj;
            }
        }
        return $indexes;
    }

    public function getConstraints()
    {
        if (empty($this->meta['child']['indexes'])) {
            return array();
        }

        $contraints = array();
        foreach ($this->meta['child']['indexes'] as $index) {
            if (
                    $index['type'] == 'FOREIGN' && !empty($index['name']) && !empty($index['cols']) && !empty($index['ref_table']) && !empty($index['ref_cols'])
            ) {
                $fk = new ForeignKey(array_column($index['cols'], 'name'));
                $fk->references($index['ref_table'], array_column($index['ref_cols'], 'name'));
                if (!empty($index['ref_on_delete'])) {
                    $fk->onDelete($index['ref_on_delete']);
                }
                if (!empty($index['ref_on_update'])) {
                    $fk->onUpdate($index['ref_on_update']);
                }
                $contraints[$index['name']] = $fk;
            }
        }
        return $contraints;
    }

}
