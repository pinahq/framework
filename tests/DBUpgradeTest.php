<?php

use PHPUnit\Framework\TestCase;
use Pina\DB\ForeignKey;
use Pina\DB\Index;
use Pina\DB\StructureParser;
use Pina\TableDataGatewayUpgrade;

class DBUpgradeTest extends TestCase
{

    public function testParse()
    {
        $foreignKey = (new ForeignKey('parent_id'))->references('parent', 'id')->onDelete('CASCADE');
        $this->assertEquals('CONSTRAINT `fk` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`) ON DELETE CASCADE', $foreignKey->make('fk'));
        $foreignKey = (new ForeignKey(['parent_id', 'parent_id2']))->references('parent2', ['id', 'id2'])->onDelete('CASCADE');
        $this->assertEquals('CONSTRAINT `fk` FOREIGN KEY (`parent_id`,`parent_id2`) REFERENCES `parent2` (`id`,`id2`) ON DELETE CASCADE', $foreignKey->make('fk'));

        $tableCondition = <<<SQL
CREATE TABLE `child` (
  `id` int(11) DEFAULT NULL AUTOINCREMENT PRIMARY KEY COLLATE 'cp1251',
  `parent_id` int(11) DEFAULT NULL,
  `parent_id2` int(11) DEFAULT NULL,
  `status` enum('Y','N') DEFAULT 'N',
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
        $parser = new StructureParser($tableCondition);
        $existedConstraints = $parser->getConstraints();
        $this->assertEquals('CONSTRAINT `child_ibfk_1` FOREIGN KEY (`parent_id`,`ddd`) REFERENCES `parent` (`id`,`eee`) ON DELETE CASCADE', $existedConstraints['child_ibfk_1']->make('child_ibfk_1'));
        $this->assertEquals('CONSTRAINT `child_ibfk_2` FOREIGN KEY (`parent_id2`) REFERENCES `parent` (`id`)', $existedConstraints['child_ibfk_2']->make('child_ibfk_2'));
        
        $existedIndexes = $parser->getIndexes();
        
        $existedFields = $parser->getFields();
        foreach ($existedFields as $f) {
            echo $f->make()."\n";
        }
        
        $existedStructure = new \Pina\DB\Structure();
        $existedStructure->setFields($existedFields);
        $existedStructure->setIndexes($existedIndexes);
        $existedStructure->setConstraints($existedConstraints);
        
        $gatewayContraints = array();
        $gatewayContraints[] = (new ForeignKey('parent_id2'))->references('parent', 'id');
        $gatewayContraints[] = (new ForeignKey('parent_id2'))->references('parent2', 'id');
        
        $gatewayIndexes = array();
        $gatewayIndexes[] = (new Index('id2'))->type('PRIMARY');
        $gatewayIndexes[] = (new Index('parent_id2'))->type('FULLTEXT');
        
        $structure = new \Pina\DB\Structure;
        $structure->setConstraints($gatewayContraints);
        $structure->setIndexes($gatewayIndexes);
        $conditions = $structure->makePathTo($existedStructure);
        
        $this->assertContains('DROP FOREIGN KEY `child_ibfk_1`', $conditions);
        $this->assertContains('ADD CONSTRAINT FOREIGN KEY (`parent_id2`) REFERENCES `parent2` (`id`)', $conditions);
        $this->assertContains('DROP PRIMARY KEY', $conditions);
        $this->assertContains('ADD PRIMARY KEY (`id2`)', $conditions);
        
        
        
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8'
SQL;
    }

}
