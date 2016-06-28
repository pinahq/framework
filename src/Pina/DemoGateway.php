<?php

namespace Pina;

class DemoGateway extends TableDataGateway
{
    public $table = "cody_demo";
    public $primaryKey = "image_id";
    public $fields = array(
        'image_id' => "INT(11) NOT NULL AUTO_INCREMENT",
        'site_id' => "INT(11) NOT NULL default '0'",
        'original_image_id' => "INT(11) NOT NULL default '0'",
        'image_hash' => "VARCHAR(128) NOT NULL default ''",
        'image_filename' => "VARCHAR(255) NOT NULL default ''",
        'image_url' => "VARCHAR(255) NOT NULL default ''",
		'image_original_url' => "VARCHAR(255) NOT NULL default ''",
        'image_width' => "INT(1) NOT NULL default '0'",
        'image_height' => "INT(1) NOT NULL default '0'",
        'image_type' => "VARCHAR(32) NOT NULL default ''",
        'image_size' => "INT(11) NOT NULL default '0'",
        'image_alt' => "varchar(120) NOT NULL DEFAULT ''",
        'image_updated' => "TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'",
    );
    public $indexes = array(
        'PRIMARY KEY' => 'image_id',
        'KEY site_id' =>  'site_id'
    );
    
    public $triggers = array(
        'update' => array('before update', 'SET NEW.image_updated = NOW()'),
    );
}
