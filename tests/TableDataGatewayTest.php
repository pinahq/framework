<?php

use PHPUnit\Framework\TestCase;
use Pina\Config;
use Pina\DB;
use Pina\SQL;
use Pina\ModuleGateway;

class TableDataGatewayTest extends TestCase
{

    public function testSelect()
    {
        Config::init(__DIR__ . '/config');

        $this->assertEquals(
            "`module`.`title`, `module`.`namespace`, `module`.`enabled`, `module`.`created`", ModuleGateway::instance()->selectAllExcept('id')->makeFields()
        );

        $this->assertEquals(
            [
                ['enum', 'enabled', ['Y', 'N'], '!!!'],
                ['length', 'title', 255, 1000],
            ], ModuleGateway::instance()->validate([
                'enabled' => '!!!',
                'title' => str_repeat('1', 1000)
            ])
        );
    }

}
