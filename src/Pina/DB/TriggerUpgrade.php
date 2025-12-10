<?php

namespace Pina\DB;

use Pina\App;
use Pina\Arr;
use Pina\Log;

class TriggerUpgrade {
    
    const FIELD_TABLE = 0;
    const FIELD_ACTION = 1;
    const FIELD_STATEMENT = 2;
    
    public static function getUpgrades($triggers)
    {
        if (!is_array($triggers)) {
            Log::error('tables', 'WRONG TRIGGER FORMAT');
            return array();
        }
        
        $existedTriggers = App::db()->table("SHOW TRIGGERS");
        foreach ($existedTriggers as $k => $v) {
            $existedTriggers[$k]['key'] = static::makeTriggerName(
                $v['Table'], $v['Timing'].' '.$v['Event']
            );
        }
        $existedTriggers = Arr::groupUnique($existedTriggers, 'key');
        #print_r($existedTriggers);exit;
        
        $createQueries = [];
        $dropQueries = [];
        
        $triggers = Arr::group($triggers, self::FIELD_TABLE);
        
        
        foreach ($triggers as $table => $ts) {
            
            $ts = Arr::groupColumn($ts, self::FIELD_ACTION, self::FIELD_STATEMENT);
            
            foreach ($ts as $action => $statements) {
                foreach ($statements as $k => $v) {
                    $statements[$k] = rtrim(trim($statements[$k]), ';').';';
                }
                
                $statement = 'BEGIN '.implode('', $statements).' END';
                
                $realName = static::makeTriggerName($table, $action);
                
                if (!isset($existedTriggers[$realName])) {
                    $createQueries = array_merge($createQueries, static::getCreateTriggerQueries($table, $action, $statement));
                    continue;
                }
                
                $needToChange = strtolower($existedTriggers[$realName]['Statement']) != strtolower($statement);
                
                if ($needToChange) {
                    $dropQueries [] = 'DROP TRIGGER ' . $existedTriggers[$realName]['Trigger'];
                    $createQueries = array_merge($createQueries, static::getCreateTriggerQueries($table, $action, $statement));
                }
                
                unset($existedTriggers[$realName]);
            }
            
        }
        
        foreach ($existedTriggers as $realName => $existedTrigger) {
            $dropQueries [] = 'DROP TRIGGER ' . $existedTrigger['Trigger'];
        }
        
        return array_merge($dropQueries, $createQueries);
    }

    protected static function getCreateTriggerQueries($table, $action, $statement)
    {
        $r = [];
        $r[] = 'CREATE TRIGGER ' . static::makeTriggerName($table, $action) . ' ' . $action . ' ON `' . $table . '` FOR EACH ROW ' . $statement;
        return $r;
    }

    protected static function makeTriggerName($table, $action)
    {
        return str_replace(' ', '_', strtolower($table . ' ' . $action));
    }
}