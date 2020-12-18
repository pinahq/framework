<?php

use PHPUnit\Framework\TestCase;
use Pina\Components\Schema;
use Pina\Components\Field;

class SchemaTest extends TestCase
{

    public function testException()
    {
        
        $schema = new Schema();
        $schema->add(Field::make('order_id', 'Заказ'));
        $schema->add(Field::make('name', 'ФИО'));
        $schema->pushProcessor(function($item) {
            $item['order_id'] = '<a href="/orders/'.$item['order_id'].'">'.$item['order_id'].' at '.$item['date'].'</a>';
            return $item;
        });
        
        $line = ['order_id' => '12', 'date' => '12.12.2020', 'name' => 'Ivan Ivanov'];
        $actual = $schema->makeFlatLine($line);
        $this->assertEquals(['<a href="/orders/12">12 at 12.12.2020</a>', 'Ivan Ivanov'], $actual);
        
        $schema->forgetField('order_id');
        $actual = $schema->makeFlatLine($line);
        $this->assertEquals(['Ivan Ivanov'], $actual);
        
        $schema->forgetField('123');
        $actual = $schema->makeFlatLine($line);
        $this->assertEquals(['Ivan Ivanov'], $actual);
        
        $schema->forgetField('name');
        $actual = $schema->makeFlatLine($line);
        $this->assertEquals([], $actual);
        
        
    }
}