<?php

use Pina\Config;
use Pina\DB;
use Pina\SQL;
use Pina\ModuleGateway;

class TableDataGatewayTest extends PHPUnit_Framework_TestCase
{
    
    public function testSelect()
    {
        Config::init(__DIR__.'/config');
        
        $this->assertEquals(
            "cody_module.module_title, cody_module.module_namespace, cody_module.module_enabled, cody_module.module_created",
            ModuleGateway::instance()->selectAllExcept('module_id')->makeFields()
        );
    }
        
}
