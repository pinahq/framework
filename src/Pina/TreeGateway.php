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

    public function addNode($parent, $id)
    {
        $parent = intval($parent);
        $id = intval($id);
        if ($id == 0 || $parent == 0) {
            return false;
        }

        return $this->db->query("
            INSERT INTO `$this->table` 
                (`{$this->treeFields['parent']}`, 
                `{$this->treeFields['id']}`, 
                `{$this->treeFields['length']}`
                ". (!empty($this->useSiteId) ? ", `site_id`) " : ")") ."
            SELECT `{$this->treeFields['parent']}`, 
                $id,
                `{$this->treeFields['length']}` + 1
                ". (!empty($this->useSiteId) ? ", `site_id` " : "") ."
            FROM `$this->table`
            WHERE `{$this->treeFields['id']}` = $parent
            ". (!empty($this->useSiteId) ? " AND `site_id` = $this->siteId " : "") ."
            UNION
            SELECT $parent, $id, 1
            ". (!empty($this->useSiteId) ? ", $this->siteId" : "") ."
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
            JOIN `$this->table` t2 
                ON `t1`.`{$this->treeFields['id']}` = `t2`.`{$this->treeFields['id']}` 
                AND `t2`.`{$this->treeFields['parent']}` = $id
                ". (!empty($this->useSiteId) ? " AND `t2`.`site_id` = $this->siteId " : "") ."
            JOIN `$this->table` t3 
                ON `t1`.`{$this->treeFields['parent']}` = `t3`.`{$this->treeFields['parent']}` 
                AND `t3`.`{$this->treeFields['id']}` = $id
                ". (!empty($this->useSiteId) ? " AND `t3`.`site_id` = $this->siteId " : "") ."
            ". (!empty($this->useSiteId) ? " WHERE `t1`.`site_id` = $this->siteId " : "") ."
        ");

        $this->db->query("
            DELETE FROM `$this->table`
            WHERE `{$this->treeFields['id']}` = $id
            ". (!empty($this->useSiteId) ? " AND `site_id` = $this->siteId " : "") ."
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
                `{$this->treeFields['length']}`
                ". (!empty($this->useSiteId) ? ", `site_id`) " : ")") ."
            SELECT `{$this->treeFields['parent']}`,
                $id,
                `{$this->treeFields['length']}` + 1
                ". (!empty($this->useSiteId) ? ", `site_id` " : "") ."
            FROM `$this->table`
            WHERE `{$this->treeFields['id']}` = $parent
            UNION
            SELECT $parent, $id, 1
            ". (!empty($this->useSiteId) ? ", $this->siteId " : "") ."
        ");

        $this->db->query("
            INSERT INTO `$this->table`
                (`{$this->treeFields['parent']}`,
                `{$this->treeFields['id']}`,
                `{$this->treeFields['length']}`
                ". (!empty($this->useSiteId) ? ", `site_id`) " : ")") ."
            SELECT `t1`.`{$this->treeFields['parent']}`,
            `t2`.`{$this->treeFields['id']}`,
            `t1`.`{$this->treeFields['length']}`
                + `t2`.`{$this->treeFields['length']}`
            ". (!empty($this->useSiteId) ? ", $this->siteId " : "") ."
            FROM `$this->table` AS `t1`
                CROSS JOIN `$this->table` AS `t2`
            WHERE `t1`.`{$this->treeFields['id']}` = $id
            AND `t2`.`{$this->treeFields['parent']}` = $id
            ". (!empty($this->useSiteId) ? "
                AND `t1`.`site_id` = $this->siteId
                AND `t2`.`site_id` = $this->siteId " : "") ."
        ");
    }
    
    public function transferNode($parent, $id)
    {
        $this->unbindNode($id);
        $this->bindNode($parent, $id);
    }
}
