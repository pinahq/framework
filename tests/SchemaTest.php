<?php

use PHPUnit\Framework\TestCase;
use Pina\BadRequestException as BadRequestExceptionAlias;
use Pina\Components\Schema;
use Pina\Events\Cron\CronEventGateway;
use Pina\Html;

class SchemaTest extends TestCase
{

    public function testException()
    {
        $schema = new Schema();
        $schema->add('order_id', 'Заказ');
        $schema->add('name', 'ФИО');
        $schema->pushHtmlProcessor(
            function ($item, $raw) {
                $item['order_id'] = Html::a($raw['order_id'] . ' at ' . $raw['date'], '/orders/' . $raw['order_id']);
                return $item;
            }
        );

        $line = ['order_id' => '12', 'date' => '12.12.2020', 'name' => 'Ivan Ivanov'];
        $actual = $schema->makeFlatLine($schema->processLineAsHtml($line));
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

    public function testValidate()
    {
        $schema = new Schema();
        $schema->add('order_id', 'Номер заказа', 'string', true);
        $schema->add('name', 'ФИО', 'string');

        $normalized = $schema->normalize(
            [
                'order_id' => 12,
                'name' => str_repeat('A', 512),
            ]
        );

        $this->assertEquals(str_repeat('A', 512), $normalized['name']);

        $this->expectException(BadRequestExceptionAlias::class);
        $normalized = $schema->normalize(
            [
                'order_id' => 12,
                'name' => str_repeat('A', 513),
            ]
        );
        try {
            $normalized = $schema->normalize(
                [
                    'order_id' => 12,
                    'name' => str_repeat('A', 513),
                ]
            );
        } catch (BadRequestExceptionAlias $e) {
            $this->assertEquals(
                [['Укажите значение короче. Максимальная длина 512 символов', 'name']],
                $e->getErrors()
            );
        }
    }

    public function testMerge()
    {
        $schema = new Schema();
        $schema->add('test1', 'Title1');
        $schema->add('test2', 'Title2');
        $schema2 = new Schema();
        $schema2->add('test3', 'Title3');
        $schema2->add('test4', 'Title4');
        $schema3 = new Schema();
        $schema3->add('test5', 'Title5');
        $schema3->add('test6', 'Title6');
        $schema->addGroup($schema2);
        $schema2->addGroup($schema3);

        $expected = ['test1', 'test2', 'test3', 'test4', 'test5', 'test6'];
        $this->assertEquals($expected, $schema->getKeys());

        $expected = ['Title1', 'Title2', 'Title3', 'Title4', 'Title5', 'Title6'];
        $this->assertEquals($expected, $schema->getTitles());

        $expected = ['', '', '', '', '', ''];
        $this->assertEquals($expected, $schema->getTypes());

        $this->assertEquals(6, $schema->getVolume());
    }

    public function testSQL()
    {
        $schema = new Schema();
        $schema->add('id', 'ID', 'int');
        $schema->add('title', 'Title', 'string');
        $schema->add('price', 'Price', 'numeric');
        $schema->add('description', 'Description', 'text');
        $schema->add('enabled', 'Enabled', 'bool');
        $schema->add('filter', 'Filter', [['id' => 'new', 'title' => 'New'], ['id' => 'old', 'title' => 'Old']]);
        $schema->add('filter2', 'Filter', [['id' => 'new', 'title' => 'New'], ['id' => 'old', 'title' => 'Old']], true,'new');

        $fields = $schema->makeSQLFields(['title' => "varchar(255) NOT NULL DEFAULT ''"]);
        $expected = [
            'id' => "int(11) NOT NULL DEFAULT 0",
            'title' => "varchar(255) NOT NULL DEFAULT ''",
            'price' => "decimal(12,2) NOT NULL DEFAULT 0",
            'description' => "mediumtext DEFAULT NULL",
            'enabled' => "enum('Y','N') NOT NULL DEFAULT 'N'",
            'filter' => "enum('new','old') NOT NULL",
            'filter2' => "enum('new','old') NOT NULL DEFAULT 'new'",
        ];
        $this->assertEquals($expected, $fields);
    }

    public function testGateway()
    {
        $expected = [
            'id' => "varchar(36) NOT NULL DEFAULT ''",
            'event' => "varchar(512) NOT NULL DEFAULT ''",
            'data' => "mediumblob DEFAULT NULL",
            'priority' => "int(11) NOT NULL DEFAULT 0",
            'delay' => "int(11) NOT NULL DEFAULT 0",
            'worker_id' => "int(11) DEFAULT NULL",
            'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
            'scheduled_at' => "timestamp DEFAULT NULL",
            'started_at' => "timestamp DEFAULT NULL",
        ];
        $schema = CronEventGateway::instance()->getSchema();
        $fields = $schema->makeSQLFields();
        $this->assertEquals($expected, $fields);

    }
}