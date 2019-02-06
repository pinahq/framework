<?php

namespace Pina;

class TableStructureParser
{

    protected $meta = null;

    public function __construct($tableCondition)
    {
        $parser = new \iamcal\SQLParser();
        $this->meta = $parser->parse($tableCondition);
    }

    public function getConstraints()
    {
        if (empty($this->meta['child']['indexes'])) {
            return array();
        }

        $contraints = array();
        foreach ($this->meta['child']['indexes'] as $index) {
            if ($index['type'] == 'FOREIGN') {
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
