<?php

use PHPUnit\Framework\TestCase;
use Pina\DB\ForeignKey;
use Pina\DB\Index;
use Pina\DB\Field;
use Pina\DB\StructureParser;

class DBUpgradeTest extends TestCase
{

    public function testStructureParser()
    {
        $parser = new StructureParser();
        $field1 = $parser->parseField('`data` longblob');
        $field2 = $parser->parseField('`data` longblob default NULL');
        $this->assertEquals($field1->make(), $field2->make());
        $field = $parser->parseField("`balance` DECIMAL(12,4) NOT NULL DEFAULT '0.0000'");
        $this->assertEquals("`balance` DECIMAL(12,4) NOT NULL DEFAULT '0.0000'", $field->make());
        $field = $parser->parseField("`id` int(10) NOT NULL AUTO_INCREMENT");
        $this->assertEquals("`id` INT(10) NOT NULL AUTO_INCREMENT", $field->make());
        $field = $parser->parseField("`ip` INT(10) UNSIGNED NOT NULL DEFAULT 0");
        $this->assertEquals("`ip` INT(10) UNSIGNED NOT NULL DEFAULT '0'", $field->make());
        $field = $parser->parseField("`ip` INT(10) UNSIGNED ZEROFILL NOT NULL DEFAULT 0");
        $this->assertEquals("`ip` INT(10) UNSIGNED ZEROFILL NOT NULL DEFAULT '0'", $field->make());
    }

    public function testParse()
    {
        $foreignKey = (new ForeignKey('parent_id'))->references('parent', 'id')->onDelete('CASCADE');
        $this->assertEquals('CONSTRAINT FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`) ON DELETE CASCADE', $foreignKey->make());
        $foreignKey = (new ForeignKey(['parent_id', 'parent_id2']))->references('parent2', ['id', 'id2'])->onDelete('CASCADE');
        $this->assertEquals('CONSTRAINT FOREIGN KEY (`parent_id`,`parent_id2`) REFERENCES `parent2` (`id`,`id2`) ON DELETE CASCADE', $foreignKey->make());

        $tableCondition = <<<SQL
CREATE TABLE `child` (
  `id` int(11) DEFAULT NULL AUTOINCREMENT PRIMARY KEY COLLATE 'cp1251',
  `parent_id` int(11) DEFAULT NULL,
  `parent_id2` int(11) DEFAULT NULL,
  `status` enum('Y','N') DEFAULT 'N',
  `title` varchar(128) NOT NULL DEFAULT '',
  `text` TEXT,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY `par_ind` (`parent_id`),
  FULLTEXT `text` (`parent_id2`),
  UNIQUE KEY `parent_id2` (`parent_id2`),
  CONSTRAINT `child_ibfk_1` FOREIGN KEY (`parent_id`, ddd) REFERENCES `parent` (`id`, eee) ON DELETE CASCADE,
  CONSTRAINT `child_ibfk_2` FOREIGN KEY (`parent_id2`) REFERENCES `parent` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
SQL;
        $parser = new StructureParser();
        $parser->parse($tableCondition);
        $existedForeignKeys = $parser->getForeignKeys();
        $this->assertEquals('CONSTRAINT FOREIGN KEY (`parent_id`,`ddd`) REFERENCES `parent` (`id`,`eee`) ON DELETE CASCADE', $existedForeignKeys['child_ibfk_1']->make());
        $this->assertEquals('CONSTRAINT FOREIGN KEY (`parent_id2`) REFERENCES `parent` (`id`)', $existedForeignKeys['child_ibfk_2']->make());

        $existedIndexes = $parser->getIndexes();

        $existedStructure = $parser->getStructure();

        $gatewayForeignKeys = array();
        $gatewayForeignKeys[] = (new ForeignKey('parent_id2'))->references('parent', 'id');
        $gatewayForeignKeys[] = (new ForeignKey('parent_id2'))->references('parent2', 'id');

        $gatewayIndexes = array();
        $gatewayIndexes[] = (new Index('id2'))->type('PRIMARY');
        $gatewayIndexes[] = (new Index('parent_id2'))->type('FULLTEXT');
        $gatewayIndexes[] = (new Index('parent_id3'));
        $gatewayIndexes[] = (new Index('parent_id4'))->type('INDEX');

        $gatewayFields = array();
        $gatewayFields[] = (new Field())->name('parent_id')->type('int')->length(11)->def('NULL');

        $structure = new \Pina\DB\Structure;
        $structure->setFields($gatewayFields);
        $structure->setIndexes($gatewayIndexes);
        $structure->setForeignKeys($gatewayForeignKeys);
        $path = $structure->makeAlterTable('tbl', $existedStructure);
        $pathDropFK = $structure->makeAlterTableDropForeignKeys('tbl', $existedStructure);
        $pathAddFK = $structure->makeAlterTableAddForeignKeys('tbl', $existedStructure);

        $this->assertContains('DROP COLUMN `id`', $path);
        $this->assertContains('DROP FOREIGN KEY `child_ibfk_1`', $pathDropFK);
        $this->assertContains('ADD CONSTRAINT FOREIGN KEY (`parent_id2`) REFERENCES `parent2` (`id`)', $pathAddFK);
        $this->assertContains('DROP PRIMARY KEY', $path);
        $this->assertContains('ADD PRIMARY KEY (`id2`)', $path);
        $this->assertContains('ADD KEY (`parent_id3`)', $path);
        $this->assertContains('ADD KEY (`parent_id4`)', $path);
    }

    public function testResourceUpgrade()
    {
        $tableCondition = <<<SQL
CREATE TABLE `resource` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) NOT NULL DEFAULT '0',
  `resource` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `resource_type_id` int(10) NOT NULL DEFAULT '0',
  `enabled` enum('Y','N') NOT NULL DEFAULT 'Y',
  `image_id` int(10) NOT NULL DEFAULT '0',
  `content_id` int(10) NOT NULL DEFAULT '0',
  `external_id` varchar(255) NOT NULL DEFAULT '',
  `order` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `resource` (`resource`),
  KEY `parent_id` (`parent_id`),
  KEY `enabled` (`enabled`),
  KEY `ord` (`order`),
  KEY `resource_type_enabled` (`resource_type_id`,`enabled`),
  FULLTEXT KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8
SQL;

        $parser = new StructureParser();
        $parser->parse($tableCondition);
        $existedStructure = $parser->getStructure();

        $fields = array(
            'id' => "int(10) NOT NULL AUTO_INCREMENT",
            'parent_id' => "int(10) NOT NULL DEFAULT 0",
            'resource' => "varchar(255) NOT NULL DEFAULT ''",
            'title' => "varchar(255) NOT NULL DEFAULT ''",
            'resource_type_id' => "int(10) NOT NULL DEFAULT 0",
            'enabled' => "enum('Y','N') NOT NULL DEFAULT 'Y'",
            'media_id' => "int(10) NOT NULL DEFAULT 0",
            'content_id' => "int(10) NOT NULL DEFAULT 0",
            'external_id' => "varchar(255) NOT NULL DEFAULT ''",
            'order' => "INT(10) NOT NULL DEFAULT 0",
        );
        $indexes = array(
            'PRIMARY KEY' => 'id',
            'UNIQUE KEY resource' => 'resource',
            'KEY parent_id' => 'parent_id',
            'KEY enabled' => 'enabled',
            'KEY ord' => 'order',
            'KEY resource_type_enabled' => ['resource_type_id', 'enabled'],
            'FULLTEXT title' => 'title'
        );
        $foreignKeys = array(
            (new ForeignKey('media_id'))->references('media', 'id'),
        );

        $parser = new StructureParser();

        $structure = new \Pina\DB\Structure;
        $structure->setFields($parser->parseGatewayFields($fields));
        $structure->setIndexes($parser->parseGatewayIndexes($indexes));
        $structure->setForeignKeys($foreignKeys);

        $path = $structure->makeAlterTable('resource', $existedStructure);
        $this->assertContains($c1 = 'DROP COLUMN `image_id`', $path);
        $this->assertContains($c2 = "ADD COLUMN `media_id` INT(10) NOT NULL DEFAULT '0'", $path);
        $this->assertContains($c3 = "ADD KEY (`media_id`)", $path);
        
        $pathDropFK = $structure->makeAlterTableDropForeignKeys('resource', $existedStructure);
        $pathAddFK = $structure->makeAlterTableAddForeignKeys('resource', $existedStructure);

        $this->assertContains($c4 = "ADD CONSTRAINT FOREIGN KEY (`media_id`) REFERENCES `media` (`id`)", $pathAddFK);

        $this->assertEquals('ALTER TABLE `resource` ' . $c1 . ', ' . $c2 . ', ' . $c3, $path);
        $this->assertEquals('', $pathDropFK);
        $this->assertEquals('ALTER TABLE `resource` ' . $c4, $pathAddFK);

        $newTableCondition = <<<SQL
CREATE TABLE IF NOT EXISTS `resource` (
  `id` INT(10) NOT NULL AUTO_INCREMENT,
  `parent_id` INT(10) NOT NULL DEFAULT '0',
  `resource` VARCHAR(255) NOT NULL DEFAULT '',
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  `resource_type_id` INT(10) NOT NULL DEFAULT '0',
  `enabled` ENUM('Y','N') NOT NULL DEFAULT 'Y',
  `media_id` INT(10) NOT NULL DEFAULT '0',
  `content_id` INT(10) NOT NULL DEFAULT '0',
  `external_id` VARCHAR(255) NOT NULL DEFAULT '',
  `order` INT(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`resource`),
  KEY (`parent_id`),
  KEY (`enabled`),
  KEY (`order`),
  KEY (`resource_type_id`,`enabled`),
  FULLTEXT KEY (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;
        $this->assertEquals($newTableCondition, $structure->makeCreateTable('resource'));
        $this->assertEquals(
            'ALTER TABLE `resource` ADD CONSTRAINT FOREIGN KEY (`media_id`) REFERENCES `media` (`id`)', $structure->makeCreateForeignKeys('resource')
        );
    }

    public function testCronEventUpgrade()
    {
        $tableCondition = <<<SQL
CREATE TABLE `queue` (
  `id` varchar(64) NOT NULL DEFAULT '',
  `handler` varchar(128) NOT NULL DEFAULT '',
  `payload` longblob,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;

        $parser = new StructureParser();
        $parser->parse($tableCondition);
        $existedStructure = $parser->getStructure();

        $fields = array(
            'id' => "varchar(64) NOT NULL DEFAULT ''",
            'handler' => "VARCHAR(128) NOT NULL default ''",
            'payload' => "longblob default NULL",
            'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
        );
        $indexes = array(
            'PRIMARY KEY' => 'id',
            'KEY created_at' => 'created_at',
        );

        $parser = new StructureParser();

        $structure = new \Pina\DB\Structure;
        $structure->setFields($parser->parseGatewayFields($fields));
        $structure->setIndexes($parser->parseGatewayIndexes($indexes));

        $conditions = $structure->makePathTo($existedStructure);

        $this->assertEmpty($conditions);
    }

    public function testConfigUpgrade()
    {
        $tableCondition = <<<SQL
CREATE TABLE `config` (
  `namespace` varchar(255) NOT NULL DEFAULT '',
  `group` varchar(32) NOT NULL DEFAULT '',
  `key` varchar(32) NOT NULL DEFAULT '',
  `value` text,
  `type` enum('text','textarea','select','checkbox','image') NOT NULL DEFAULT 'text',
  `variants` varchar(1000) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `resource` varchar(255) NOT NULL DEFAULT '',
  `order` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`namespace`,`key`),
  UNIQUE KEY `namespace` (`namespace`,`group`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;

        $parser = new StructureParser();
        $parser->parse($tableCondition);
        $existedStructure = $parser->getStructure();

        $fields = array(
            'namespace' => "varchar(255) NOT NULL DEFAULT ''",
            'group' => "varchar(32) NOT NULL DEFAULT ''",
            'key' => "varchar(32) NOT NULL DEFAULT ''",
            'value' => "text DEFAULT NULL",
            'type' => "enum('text','textarea','select','checkbox','image') NOT NULL DEFAULT 'text'",
            'variants' => "varchar(1000) NOT NULL DEFAULT ''",
            'title' => "varchar(255) NOT NULL DEFAULT ''",
            'resource' => "varchar(255) NOT NULL DEFAULT ''",
            'order' => "int(1) NOT NULL DEFAULT '0'"
        );
        $indexes = array(
            'PRIMARY KEY' => ['namespace', 'key'],
            'UNIQUE KEY group_key' => ['namespace', 'group', 'key']
        );

        $parser = new StructureParser();

        $structure = new \Pina\DB\Structure;
        $structure->setFields($parser->parseGatewayFields($fields));
        $structure->setIndexes($parser->parseGatewayIndexes($indexes));

        $path = $structure->makeAlterTable('config', $existedStructure);

        $this->assertEquals('', $path);
    }

    public function testUserConstraintUpgrade()
    {
        $tableCondition = <<<SQL
CREATE TABLE `cons` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '0',
  `description` varchar(256) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `cons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8
SQL;

        $parser = new StructureParser();
        $parser->parse($tableCondition);
        $existedStructure = $parser->getStructure();

        $fields = array(
            'id' => "int(10) NOT NULL AUTO_INCREMENT",
            'user_id' => "int(10) NOT NULL DEFAULT '0'",
            'description' => "varchar(256) NOT NULL DEFAULT ''",
            'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
        );
        $indexes = array(
            'PRIMARY KEY' => 'id',
        );
        $foreignKeys = array(
                (new \Pina\DB\ForeignKey('user_id'))
                ->references('user', 'id')
        );

        $parser = new StructureParser();

        $structure = new \Pina\DB\Structure;
        $structure->setFields($parser->parseGatewayFields($fields));
        $structure->setIndexes($parser->parseGatewayIndexes($indexes));
        $structure->setForeignKeys($foreignKeys);

        $path = $structure->makeAlterTable('cons', $existedStructure);

        $this->assertEquals('', $path);
    }

}
