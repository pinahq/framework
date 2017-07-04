<?php

namespace Pina;

class ModuleGateway extends TableDataGateway
{

    protected static $table = "cody_module";
    protected static $fields = array(
        'module_id' => "INT(11) NOT NULL AUTO_INCREMENT",
        'module_title' => "VARCHAR(255) NOT NULL default ''",
        'module_namespace' => "VARCHAR(128) NOT NULL default ''",
        'module_enabled' => "enum('Y','N') NOT NULL default 'N'",
        'module_created' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
    );
    protected static $indexes = array(
        'PRIMARY KEY' => 'module_id',
        'KEY module_namespace' => 'module_namespace',
    );

}
