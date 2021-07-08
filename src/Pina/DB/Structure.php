<?php

namespace Pina\DB;

class Structure
{

    protected $fields = array();
    protected $indexes = array();
    protected $foreignKeys = array();
    protected $engine = 'InnoDB';
    protected $charset = 'utf8';

    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $indexes
     */
    public function setIndexes($indexes)
    {
        $this->indexes = $indexes;
    }

    /**
     * @return array
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @param array $foreignKeys
     */
    public function setForeignKeys($foreignKeys)
    {
        $this->foreignKeys = $foreignKeys;
    }

    /**
     * @return array
     */
    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }

    /**
     * @param string $engine
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    /**
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param string $charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $name
     * @return string
     */
    public function makeCreateTable($name)
    {
        $schema = array_merge($this->getFields(), $this->getIndexes());
        $strings = array_map(array($this, 'callMake'), $schema);
        $engine = 'ENGINE=' . $this->getEngine();
        $charset = 'DEFAULT CHARSET=' . $this->getCharset();

        $query = 'CREATE TABLE IF NOT EXISTS `' . $name . '`';
        $query .= ' (' . "\n  " . implode(",\n  ", $strings) . "\n" . ') ';
        $query .= $engine . ' ' . $charset;
        return $query;
    }

    /**
     * @param string $table
     * @return string
     */
    public function makeCreateForeignKeys($table)
    {
        $conditions = $this->makeIndexPath(array(), $this->foreignKeys);
        if (empty($conditions)) {
            return '';
        }
        return 'ALTER TABLE `' . $table . '` ' . implode(', ', $conditions);
    }

    /**
     * @param string $table
     * @param Structure $existedStructure
     * @return string
     */
    public function makeAlterTable($table, $existedStructure)
    {
        $conditions = $this->makePathTo($existedStructure);
        if (empty($conditions)) {
            return '';
        }
        return 'ALTER TABLE `' . $table . '` ' . implode(', ', $conditions);
    }

    /**
     * @param string $table
     * @param Structure $existedStructure
     * @return string
     */
    public function makeAlterTableForeignKeys($table, $existedStructure)
    {
        $conditions = $this->makeIndexPath($existedStructure->getforeignKeys(), $this->foreignKeys);
        if (empty($conditions)) {
            return '';
        }
        return 'ALTER TABLE `' . $table . '` ' . implode(', ', $conditions);
    }

    /**
     * @param string $table
     * @param Structure $existedStructure
     * @return string
     */
    public function makeAlterTableCharset($table, $existedStructure)
    {
        if ($this->getCharset() != $existedStructure->getCharset()) {
            return 'ALTER TABLE `' . $table . '` CONVERT TO CHARACTER SET '.$this->getCharset();
        }
        return '';
    }

    /**
     * @param string $table
     * @param Structure $existedStructure
     * @return string
     */
    public function makeAlterTableEngine($table, $existedStructure)
    {
        if ($this->getEngine() != $existedStructure->getEngine()) {
            return 'ALTER TABLE `' . $table . '` ENGINE '.$this->getEngine();
        }
        return '';
    }

    /**
     * @param Structure $existedStructure
     * @return array
     */
    public function makePathTo($existedStructure)
    {
        $fields = $this->makeFieldPath($existedStructure->getFields(), $this->fields);
        $indexes = $this->makeIndexPath(
            $existedStructure->getIndexes(),
            array_merge($this->indexes, $this->getConstraintIndexes())
        );
        return array_merge($fields, $indexes);
    }

    /**
     * @return array
     */
    protected function getConstraintIndexes()
    {
        $indexes = array();
        foreach ($this->foreignKeys as $foreignKey) {
            $indexes[] = new Index($foreignKey->getColumns());
        }
        return $indexes;
    }

    /**
     * @param StructureItemInterface[] $from
     * @param StructureItemInterface[] $to
     * @return array
     */
    public function makeFieldPath($from, $to)
    {
        $conditions = array();

        $gatewayStrings = array_reduce($to, array($this, 'reduceFields'), array());
        $existedStrings = array_reduce($from, array($this, 'reduceFields'), array());

        $toDelete = array_diff_key($existedStrings, $gatewayStrings);
        $toCreate = array_diff_key($gatewayStrings, $existedStrings);
        $toModify = array_intersect_key($gatewayStrings, $existedStrings);

        foreach ($toDelete as $name => $t) {
            $conditions[] = 'DROP COLUMN `' . $name . '`';
        }
        foreach ($toModify as $name => $cond) {
            if ($cond == $existedStrings[$name]) {
                continue;
            }
            $conditions[] = 'MODIFY ' . $cond;
        }
        foreach ($toCreate as $name => $cond) {
            $conditions[] = 'ADD COLUMN ' . $cond;
        }

        return $conditions;
    }

    /**
     * @param StructureItemInterface[] $from
     * @param StructureItemInterface[] $to
     * @return array
     */
    public function makeIndexPath($from, $to)
    {
        $conditions = array();
        $gatewayStrings = array_map(array($this, 'callMake'), $to);
        $existedStrings = array_map(array($this, 'callMake'), $from);
        $toDelete = array_diff($existedStrings, $gatewayStrings);
        foreach ($toDelete as $indexKey => $t) {
            $conditions[] = $from[$indexKey]->makeDrop($indexKey);
        }
        $toCreate = array_diff($gatewayStrings, $existedStrings);
        foreach ($toCreate as $indexKey => $t) {
            $conditions[] = $to[$indexKey]->makeAdd();
        }

        return $conditions;
    }

    /**
     * @param StructureItemInterface $key
     * @return string
     */
    public function callMake(StructureItemInterface $key)
    {
        return $key->make();
    }

    /**
     * @param array $carry
     * @param StructureItemInterface $field
     * @return array
     */
    public function reduceFields($carry, StructureItemInterface $field)
    {
        $carry[$field->getName()] = $field->make();
        return $carry;
    }

}
