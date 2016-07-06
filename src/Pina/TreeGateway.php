<?php

namespace Pina;

use Pina\TableDataGateway;
use Pina\SQL;

class TreeGateway extends TableDataGateway
{
    public $treeFields = array();

    public function issetChilds($id)
    {
        $id = intval($id);
        if ($id == 0) {
            return false;
        }

        return $this
            ->whereBy($this->treeFields['parent'], $id)
            ->count();
    }

    public function whereLevel($level = 0)
    {
        $level = intval($level);
        if ($level > 0) {
            $this->whereBy($this->treeFields['length'], $level);
        }

        return $this;
    }

    public function findParents($id, $level = 0)
    {
        if (is_array($id)) {
            $id = array_map('intval', $id);
        } else {
            $id = intval($id);
            if ($id == 0) {
                return false;
            }
        }

        return $this
            ->whereBy($this->treeFields['id'], $id)
            ->whereLevel($level)
            ->column($this->treeFields['parent']);
    }

    public function findChilds($id, $level = 0)
    {
        if (is_array($id)) {
            $id = array_map('intval', $id);
        } else {
            $id = intval($id);
            if ($id == 0) {
                return false;
            }
        }

        return $this->whereBy($this->treeFields['parent'], $id)
            ->whereLevel($level)
            ->column($this->treeFields['id']);
    }

    public function addNode($parentId, $id)
    {
        $parentId = intval($parentId);
        $id = intval($id);
        if ($id == 0 || $parentId == 0) {
            return false;
        }

        return $this->db->query("
            INSERT INTO `$this->table` 
                (`{$this->treeFields['parent']}`, 
                `{$this->treeFields['id']}`, 
                `{$this->treeFields['length']}`
            SELECT `{$this->treeFields['parent']}`, $id,
                `{$this->treeFields['length']}` + 1
            FROM `$this->table`
            WHERE `{$this->treeFields['id']}` = $parentId
            UNION
            SELECT $parentId, $id, 1
        ");
    }
    
    public function unbindNode($id)
    {
        $id = intval($id);
        if ($id == 0) {
            return false;
        }
        
        $this->db->query("
            DELETE `t1`
            FROM `$this->table` `t1`
            JOIN `$this->table` `t2` 
                ON `t1`.`{$this->treeFields['id']}` = `t2`.`{$this->treeFields['id']}` 
                AND `t2`.`{$this->treeFields['parent']}` = $id
            JOIN `$this->table` t3 
                ON `t1`.`{$this->treeFields['parent']}` = `t3`.`{$this->treeFields['parent']}` 
                AND `t3`.`{$this->treeFields['id']}` = $id
        ");

        $this->db->query("
            DELETE FROM `$this->table`
            WHERE `{$this->treeFields['id']}` = $id
        ");
    }

    public function bindNode($parent, $id)
    {
        $parent = intval($parent);
        $id = intval($id);
        if ($parent == 0 || $id == 0) {
            return false;
        }
        
        $this->db->query("
            INSERT INTO `$this->table`
                (`{$this->treeFields['parent']}`,
                `{$this->treeFields['id']}`,
                `{$this->treeFields['length']}`)
            SELECT `{$this->treeFields['parent']}`,
                $id,
                `{$this->treeFields['length']}` + 1
            FROM `$this->table`
            WHERE `{$this->treeFields['id']}` = $parent
            UNION
            SELECT $parent, $id, 1
        ");

        $this->db->query("
            INSERT INTO `$this->table`
                (`{$this->treeFields['parent']}`,
                `{$this->treeFields['id']}`,
                `{$this->treeFields['length']}`)
            SELECT `t1`.`{$this->treeFields['parent']}`,
            `t2`.`{$this->treeFields['id']}`,
            `t1`.`{$this->treeFields['length']}`
                + `t2`.`{$this->treeFields['length']}`
            FROM `$this->table` AS `t1`
                CROSS JOIN `$this->table` AS `t2`
            WHERE `t1`.`{$this->treeFields['id']}` = $id
            AND `t2`.`{$this->treeFields['parent']}` = $id
        ");
    }
    
    public function transferNode($parent, $id)
    {
        $this->unbindNode($id);
        $this->bindNode($parent, $id);
    }
}
