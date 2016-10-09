<?php

namespace Pina;

class DemoGateway extends TableDataGateway
{
    protected static $table = "cody_demo";
    protected static $fields = array(
        'image_id' => "INT(11) NOT NULL AUTO_INCREMENT",
        'original_image_id' => "INT(11) NOT NULL default '0'",
        'image_hash' => "VARCHAR(128) NOT NULL default ''",
        'image_filename' => "VARCHAR(255) NOT NULL default ''",
        'image_url' => "VARCHAR(255) NOT NULL default ''",
        'image_original_url' => "VARCHAR(32) NOT NULL default ''",
        'image_width' => "INT(1) NOT NULL default '0'",
        'image_height' => "INT(1) NOT NULL default '0'",
        'image_type' => "VARCHAR(32) NOT NULL default ''",
        'image_size' => "INT(11) NOT NULL default '0'",
        'image_alt' => "varchar(120) NOT NULL DEFAULT ''",
    );
    protected static $indexes = array(
        'PRIMARY KEY' => 'image_id',
    );
    
    public function getTriggers()
    {
        return [
            [
                $this->getTable(),
                'before update',
                'SET NEW.image_width = NEW.image_width + 2'
            ],
        ];
    }
}
