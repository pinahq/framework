<?php

namespace Pina\DB;

class StructureParser
{

    protected $meta = null;

    public function __construct($tableCondition)
    {
        $parser = new \iamcal\SQLParser();
        $parsed = $parser->parse($tableCondition);
        $this->meta = array_pop($parsed);
    }

    public function getIndexes()
    {
        if (empty($this->meta['indexes'])) {
            return array();
        }

        $indexes = array();
        foreach ($this->meta['indexes'] as $index) {
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
                $indexObj->type($index['type']);
                $indexes[$index['name']] = $indexObj;
            }
        }
        return $indexes;
    }

    public function getConstraints()
    {
        if (empty($this->meta['indexes'])) {
            return array();
        }

        $contraints = array();
        foreach ($this->meta['indexes'] as $index) {
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

    public function getFields()
    {
        $fields = array();
        foreach ($this->meta['fields'] as $f) {
            if (empty($f['name']) || empty($f['type'])) {
                continue;
            }
            $field = new Field();
            $field->name($f['name'])->type($f['type'])->isNull(!empty($f['null']));
            if (isset($f['length'])) {
                $field->length($f['length']);
            }
            if (isset($f['default'])) {
                $field->def($f['default']);
            }
            if (isset($f['values']) && is_array($f['values'])) {
                $field->values($f['values']);
            }
            if (isset($f['more']) && is_array($f['more'])) {
                $field->extra(implode(' ', $f['more']));
            }
            $fields[] = $field;
        }
        return $fields;
    }

}
