<?php

use PHPUnit\Framework\TestCase;
use Pina\ForeignKey;

class ForeignKeyTest extends TestCase
{

    public function testParse()
    {
        $foreignKey = ForeignKey::parse('CONSTRAINT `child_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`) ON DELETE CASCADE');
        $this->assertEquals('CONSTRAINT `fk` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`) ON DELETE CASCADE', $foreignKey->make('fk'));
        $foreignKey = ForeignKey::parse('CONSTRAINT `child_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`) ON DELETE CASCADE ON UPDATE SET DEFAULT');
        $this->assertEquals('CONSTRAINT `fk` FOREIGN KEY (`parent_id`) REFERENCES `parent` (`id`) ON DELETE CASCADE ON UPDATE SET DEFAULT', $foreignKey->make('fk'));
        $foreignKey = ForeignKey::parse('CONSTRAINT `child_ibfk_2` FOREIGN KEY (`parent_id2`) REFERENCES `parent` (`id`)');
        $this->assertEquals('CONSTRAINT `fk` FOREIGN KEY (`parent_id2`) REFERENCES `parent` (`id`)', $foreignKey->make('fk'));
    }

}
