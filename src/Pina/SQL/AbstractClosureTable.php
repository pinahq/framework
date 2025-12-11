<?php

namespace Pina\SQL;

use Pina\Data\Schema;
use Pina\TableDataGateway;
use Pina\Types\IntegerType;

use function Pina\__;

abstract class AbstractClosureTable extends TableDataGateway
{

    abstract protected function getBaseTable(): string;

    /**
     * @return Schema
     * @throws \Exception
     */
    public function getSchema(): Schema
    {
        $schema = parent::getSchema();
        $schema->add('id', 'ID', IntegerType::class);
        $schema->add('parent_id', __('Parent'), IntegerType::class);
        $schema->setPrimaryKey(['id', 'parent_id']);

        $schema->add('length', __('Length'), IntegerType::class);
        $schema->addKey(['parent_id', 'length']);
        return $schema;
    }

    public function getTriggers()
    {
        $table = $this->getTable();

        $addTreeNode = "
            IF (NEW.parent_id IS NOT NULL) THEN
                INSERT INTO $table (parent_id, id, length)
                SELECT $table.parent_id, NEW.id, $table.length + 1
                FROM $table WHERE id = NEW.parent_id
                UNION
                SELECT NEW.parent_id, NEW.id, 1;
            END IF;";

        $deleteTreeNode = "DELETE `t1` FROM $table as t1 JOIN $table t2 ON t1.id = t2.id AND t2.parent_id = OLD.id JOIN $table t3 ON t1.parent_id = t3.parent_id AND t3.id = OLD.id;";
        $deleteTreeNode .= "DELETE FROM $table WHERE id = OLD.id;";

        $updateTreeNode = "INSERT INTO $table (parent_id, id, length) SELECT t1.parent_id, t2.id, t1.length + t2.length FROM $table t1 CROSS JOIN $table t2 WHERE t1.id = NEW.id AND t2.parent_id = NEW.id;";

        $updateCondition = "OLD.parent_id <> NEW.parent_id OR (OLD.parent_id IS NULL AND NEW.parent_id IS NOT NULL) OR (OLD.parent_id IS NOT NULL AND NEW.parent_id IS NULL)";

        $baseTable = $this->getBaseTable();

        return [
            [
                $baseTable,
                'after insert',
                $addTreeNode,
            ],
            [
                $baseTable,
                'after update',
                "IF ($updateCondition) THEN " . $deleteTreeNode . $addTreeNode . $updateTreeNode . "END IF;"
            ],
            [
                $baseTable,
                'after delete',
                $deleteTreeNode . "DELETE FROM $table WHERE parent_id = OLD.id;"
            ],
        ];
    }
}