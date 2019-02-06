<?php

use PHPUnit\Framework\TestCase;
use Pina\ForeignKey;
use Pina\TableStructureParser;
use Pina\TableDataGatewayUpgrade;

class ForeignKeyTest extends TestCase
{

    public function testParse()
    {
        $foreignKey = (new ForeignKey('parent_id'))->references('parent', 'id')->onDelete('CASCADE');
        $this->assertEquals('CONSTRAINT `fk` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`) ON DELETE CASCADE', $foreignKey->make('fk'));
        $foreignKey = (new ForeignKey(['parent_id', 'parent_id2']))->references('parent2', ['id', 'id2'])->onDelete('CASCADE');
        $this->assertEquals('CONSTRAINT `fk` FOREIGN KEY (`parent_id`,`parent_id2`) REFERENCES `parent2` (`id`,`id2`) ON DELETE CASCADE', $foreignKey->make('fk'));
        $foreignKey = ForeignKey::parse('CONSTRAINT `child_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`) ON DELETE CASCADE ON UPDATE SET DEFAULT');
        $this->assertEquals('CONSTRAINT `fk` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`) ON DELETE CASCADE ON UPDATE SET DEFAULT', $foreignKey->make('fk'));
        $foreignKey = ForeignKey::parse('CONSTRAINT `child_ibfk_2` FOREIGN KEY (`parent_id2`) REFERENCES `parent` (`id`)');
        $this->assertEquals('CONSTRAINT `fk` FOREIGN KEY (`parent_id2`) REFERENCES `parent` (`id`)', $foreignKey->make('fk'));

        $tableCondition = 'CREATE TABLE `child` (
  `id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `parent_id2` int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY `par_ind` (`parent_id`),
  KEY `parent_id2` (`parent_id2`),
  CONSTRAINT `child_ibfk_1` FOREIGN KEY (`parent_id`, ddd) REFERENCES `parent` (`id`, eee) ON DELETE CASCADE,
  CONSTRAINT `child_ibfk_2` FOREIGN KEY (`parent_id2`) REFERENCES `parent` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1';
        $existed = (new TableStructureParser($tableCondition))->getConstraints();
        $this->assertEquals('CONSTRAINT `child_ibfk_1` FOREIGN KEY (`parent_id`,`ddd`) REFERENCES `parent` (`id`,`eee`) ON DELETE CASCADE', $existed['child_ibfk_1']->make('child_ibfk_1'));
        $this->assertEquals('CONSTRAINT `child_ibfk_2` FOREIGN KEY (`parent_id2`) REFERENCES `parent` (`id`)', $existed['child_ibfk_2']->make('child_ibfk_2'));
        foreach ($existed as $name => $contraint) {
//            echo $contraint->make($name)."\n";
        }
    }

}
