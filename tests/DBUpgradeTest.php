<?php

use PHPUnit\Framework\TestCase;
use Pina\DB\ForeignKey;
use Pina\DB\Index;
use Pina\DB\Field;
use Pina\DB\StructureParser;

class DBUpgradeTest extends TestCase
{

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
        $existedConstraints = $parser->getConstraints();
        $this->assertEquals('CONSTRAINT FOREIGN KEY (`parent_id`,`ddd`) REFERENCES `parent` (`id`,`eee`) ON DELETE CASCADE', $existedConstraints['child_ibfk_1']->make());
        $this->assertEquals('CONSTRAINT FOREIGN KEY (`parent_id2`) REFERENCES `parent` (`id`)', $existedConstraints['child_ibfk_2']->make());

        $existedIndexes = $parser->getIndexes();

        $existedStructure = $parser->getStructure();

        $gatewayContraints = array();
        $gatewayContraints[] = (new ForeignKey('parent_id2'))->references('parent', 'id');
        $gatewayContraints[] = (new ForeignKey('parent_id2'))->references('parent2', 'id');

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
        $structure->setConstraints($gatewayContraints);
        $conditions = $structure->makePathTo($existedStructure);

        $this->assertContains('DROP COLUMN `id`', $conditions);
        $this->assertContains('DROP FOREIGN KEY `child_ibfk_1`', $conditions);
        $this->assertContains('ADD CONSTRAINT FOREIGN KEY (`parent_id2`) REFERENCES `parent2` (`id`)', $conditions);
        $this->assertContains('DROP PRIMARY KEY', $conditions);
        $this->assertContains('ADD PRIMARY KEY (`id2`)', $conditions);
        $this->assertContains('ADD KEY (`parent_id3`)', $conditions);
        $this->assertContains('ADD KEY (`parent_id4`)', $conditions);
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
        $constraints = array(
            (new ForeignKey('media_id'))->references('media', 'id'),
        );
        
        $parser = new StructureParser();
        
        $structure = new \Pina\DB\Structure;
        $structure->setFields($parser->parseGatewayFields($fields));
        $structure->setIndexes($parser->parseGatewayIndexes($indexes));
        $structure->setConstraints($constraints);
        
        $conditions = $structure->makePathTo($existedStructure);
        
        $this->assertContains($c1 = 'DROP COLUMN `image_id`', $conditions);
        $this->assertContains($c2 = "ADD COLUMN `media_id` INT(10) NOT NULL DEFAULT '0'", $conditions);
        $this->assertContains($c3 = "ADD CONSTRAINT FOREIGN KEY (`media_id`) REFERENCES `media` (`id`)", $conditions);
        
        $path = $structure->makeAlterTable('resource', $existedStructure);
        $this->assertEquals('ALTER TABLE `resource` '.$c1.', '.$c2.', '.$c3, $path);
        
$newTableCondition = <<<SQL
CREATE TABLE IF NOT EXISTS `resource` (
  `id` INT(10) NOT NULL,
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
  FULLTEXT KEY (`title`),
  CONSTRAINT FOREIGN KEY (`media_id`) REFERENCES `media` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;
        $this->assertEquals($newTableCondition, $structure->makeCreateTable('resource'));
        

    }

}
