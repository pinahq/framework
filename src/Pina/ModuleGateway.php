<?php

namespace Pina;

class ModuleGateway extends TableDataGateway
{

    protected static $table = "module";
    protected static $fields = array(
        'id' => "INT(11) NOT NULL AUTO_INCREMENT",
        'title' => "VARCHAR(255) NOT NULL default ''",
        'namespace' => "VARCHAR(128) NOT NULL default ''",
        'enabled' => "enum('Y','N') NOT NULL default 'N'",
        'created' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
    );
    protected static $indexes = array(
        'PRIMARY KEY' => 'id',
        'KEY namespace' => 'namespace',
    );

}
