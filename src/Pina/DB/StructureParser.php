<?php

namespace Pina\DB;

class StructureParser
{

    protected $meta = null;
    protected $parser = null;

    public function __construct()
    {
        $this->parser = new \iamcal\SQLParser();
    }

    public function parse($tableCondition)
    {
        $parsed = $this->parser->parse($tableCondition);
        $this->meta = array_pop($parsed);
    }

    public function parseGatewayFields($fields)
    {
        $r = array();
        foreach ($fields as $k => $v) {
            $r[] = $this->parseField('`' . $k . '` ' . $v);
        }
        return $r;
    }

    public function parseGatewayIndexes($indexes)
    {
        $r = array();
        foreach ($indexes as $k => $v) {
            $r[] = $this->parseIndex($k . '(' . (is_array($v) ? implode(',', $v) : $v).')');
        }
        return $r;
    }

    public function parseField($fieldCondition)
    {
        $tokens = $this->parser->lex($fieldCondition);
        $f = $this->parser->parse_field($tokens);
        return $this->makeField($f);
    }

    public function parseIndex($condition)
    {
        $tokens = $this->parser->lex($condition);
        $fields = array();
        $keys = array();
        $this->parser->parse_field_or_key($tokens, $fields, $keys);
        return $this->makeIndex(array_pop($keys));
    }

    public function getStructure()
    {
        $structure = new Structure();
        $structure->setFields($this->getFields());
        $structure->setIndexes($this->getIndexes());
        $structure->setConstraints($this->getConstraints());
        return $structure;
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
                $indexes[$index['name']] = $this->makeIndex($index);
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
            $field = $this->makeField($f);
            if (empty($field)) {
                continue;
            }
            $fields[] = $field;
        }
        return $fields;
    }

    protected function makeField($f)
    {
        if (empty($f['name']) || empty($f['type'])) {
            return null;
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
        return $field;
    }

    protected function makeIndex($index)
    {
        if (
            $index['type'] == 'FOREIGN'
        ) {
            return null;
        }
        $indexObj = new Index(array_column($index['cols'], 'name'));
        $indexObj->type($index['type']);
        return $indexObj;
    }

}
