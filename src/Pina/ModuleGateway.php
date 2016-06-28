<?php

namespace Pina;

class ModuleGateway extends TableDataGateway
{

    public $table = "cody_module";
    public $primaryKey = "module_id";
    public $fields = array(
        'module_id' => "INT(11) NOT NULL AUTO_INCREMENT",
        'site_id' => "INT(11) NOT NULL default '0'",
        'module_title' => "VARCHAR(255) NOT NULL default ''",
        'module_namespace' => "VARCHAR(128) NOT NULL default ''",
        'module_enabled' => "enum('Y','N') NOT NULL default 'N'",
        'module_created' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
    );
    public $indexes = array(
        'PRIMARY KEY' => 'module_id',
        'KEY site_id' => 'site_id',
        'KEY module_namespace' => 'module_namespace',
    );

}
