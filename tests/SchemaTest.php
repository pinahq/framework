<?php

use PHPUnit\Framework\TestCase;
use Pina\BadRequestException as BadRequestExceptionAlias;
use Pina\Controls\TableView;
use Pina\Data\DataTable;
use Pina\Data\Schema;
use Pina\Html;
use Pina\Queue\QueueGateway;
use Pina\Types\BooleanType;
use Pina\Types\IntegerType;
use Pina\Types\NumericType;
use Pina\Types\StringType;
use Pina\Types\TextType;

class SchemaTest extends TestCase
{

    /**
     * @throws Exception
     */
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

    /**
     * @throws Exception
     */
    public function testValidate()
    {
        $schema = new Schema();
        $schema->add('order_id', 'Номер заказа', StringType::class, true);
        $schema->add('name', 'ФИО', StringType::class);

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
        $this->assertEquals($expected, $schema->getFieldKeys());

        $expected = ['Title1', 'Title2', 'Title3', 'Title4', 'Title5', 'Title6'];
        $this->assertEquals($expected, $schema->getFieldTitles());

        $expected = [StringType::class, StringType::class, StringType::class, StringType::class, StringType::class, StringType::class];
        $this->assertEquals($expected, $schema->getFieldTypes());

        $this->assertEquals(6, $schema->getVolume());
    }

    /**
     * @throws Exception
     */
    public function testFieldset()
    {
        $line = $this->makeLine();
        $schema = $this->makeSchema();
        $concat = function ($a) {
            return implode(' ', $a);
        };
        $schema->fieldset(['price', 'currency'])->join($concat, 'price', 'Price');

        $this->assertEquals(['id', 'title', 'price'], $schema->getFieldKeys());

        $this->assertEquals(
            ['id' => '5', 'title' => 'Test', 'price' => '4000 RUB'],
            $schema->processLineAsText($line)
        );

//        $line = $this->makeLine();
        $schema = $this->makeSchema();
        $schema->fieldset(['price', 'currency'])->printf('%d - %s', 'test', 'Test');
        $this->assertEquals(
            ['id' => 5, 'title' => 'Test', 'price' => 4000, 'currency' => 'RUB', 'test' => '4000 - RUB'],
            $schema->processLineAsData($line)
        );

        /*
         * проведем эмуляцию постинга формы фильтрации.
         * если проверять с помощью оригинальной схемы, то пустое значение для цены из поисковой формы
         * спровоцирует ошибку, так как должно быть введено корректное число
         */
        $filter = $this->makeSchema()->fieldset(['price', 'currency'])->makeSchema();
        $errors = [];
        try {
            $filter->normalize([]);
        } catch (BadRequestExceptionAlias $e) {
            $errors = $e->getErrors();
        }
        $this->assertEquals('price', $errors[0][1] ?? '');

        /**
         * а если поле цены может быть nullable, то пустое значение для формы-фильтрации пройдет
         */
        $filter = $this->makeSchema()->fieldset(['price', 'currency'])->setNullable()->makeSchema();
        $normalized = $filter->normalize([]);
        $this->assertNull($normalized['price']);
        $this->assertNull($normalized['currency']);
    }

    public function testField()
    {
        $schema = new Schema();
        $schema->add('id', 'ID', IntegerType::class);
        $f = $schema->add('title', 'Title', StringType::class)->setDescription('Please enter description');
        $this->assertEquals('Please enter description', $f->getDescription());
    }

    private function makeLine()
    {
        return [
            'id' => 5,
            'title' => 'Test',
            'price' => 4000,
            'currency' => 'RUB'
        ];
    }

    private function makeSchema()
    {
        $schema = new Schema();
        $schema->add('id', 'ID', IntegerType::class);
        $schema->add('title', 'Title', StringType::class);
        $schema->add('price', 'Price', NumericType::class);
        $schema->add('currency', 'Currency', StringType::class);
        return $schema;
    }

    /**
     * @throws Exception
     */
    public function testSQL()
    {
        $schema = new Schema();
        $schema->add('id', 'ID', IntegerType::class);
        $schema->add('title', 'Title', StringType::class);
        $schema->add('price', 'Price', NumericType::class);
        $schema->add('description', 'Description', TextType::class);
        $schema->add('enabled', 'Enabled', BooleanType::class);
        $schema->add('filter', 'Filter', [['id' => 'new', 'title' => 'New'], ['id' => 'old', 'title' => 'Old']]);
        $schema->add('filter2', 'Filter', [['id' => 'new', 'title' => 'New'], ['id' => 'old', 'title' => 'Old']])
            ->setMandatory()->setDefault('new');

        $fields = $schema->makeSQLFields(['title' => "varchar(255) NOT NULL DEFAULT ''"]);
        $expected = [
            'id' => "int(11) NOT NULL DEFAULT 0",
            'title' => "varchar(255) NOT NULL DEFAULT ''",
            'price' => "decimal(12,2) NOT NULL DEFAULT '0.00'",
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
            'handler' => "varchar(512) NOT NULL DEFAULT ''",
            'payload' => "mediumblob DEFAULT NULL",
            'priority' => "int(11) NOT NULL DEFAULT 0",
            'delay' => "int(11) NOT NULL DEFAULT 0",
            'worker_id' => "int(11) DEFAULT NULL",
            'created_at' => "timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP",
            'scheduled_at' => "timestamp DEFAULT NULL",
            'started_at' => "timestamp DEFAULT NULL",
        ];
        $schema = QueueGateway::instance()->getSchema();
        $fields = $schema->makeSQLFields();
        $this->assertEquals($expected, $fields);

        $schema = QueueGateway::instance()
            ->select('*')
            ->getQuerySchema();

        $this->assertEquals(array_keys($expected), $schema->getFieldKeys());


        $schema = QueueGateway::instance()
            ->select('handler')
            ->innerJoin(
                QueueGateway::instance()->on('worker_id', 'worker_id')->alias('worker_tasks')
                    ->selectAs('handler', 'worker_handler')
                    ->select('payload')
                    ->calculate('CONCAT(handler, payload)', 'calculated', 'Some title')
            )
            ->getQuerySchema();

        $keys = ['handler', 'worker_handler', 'payload', 'calculated'];
        $values = range(1, count($keys));

        $this->assertEquals($keys, $schema->getFieldKeys());


        $data = new DataTable([array_combine($keys, $values)], $schema);
        $view = new TableView();
        $view->load($data);

        $header = '<tr><th>Handler</th><th>Handler</th><th>Payload</th><th>Some title</th></tr>';
        $body = '<tr><td>1</td><td>2</td><td>3</td><td>4</td></tr>';
        $html = '<div class="card"><div class="card-body"><table class="table table-hover">' . $header . $body . '</table></div></div>';

        $this->assertEquals($html, $view->__toString());
    }
}