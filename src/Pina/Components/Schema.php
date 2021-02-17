<?php

namespace Pina\Components;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use LogicException;
use Pina\BadRequestException;
use Pina\App;
use Pina\Arr;
use function Pina\__;

class Schema implements IteratorAggregate
{

    protected $title = '';

    /**
     *
     * @var Field[]
     */
    protected $fields = [];
    protected $processors = [];

    /**
     *
     * @var Schema[]
     */
    protected $groups = [];

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Добавляет в схему поле
     * @param mixed $field
     * @param string $title
     * @param string $type
     * @param bool $isMandatory
     * @return void
     */
    public function add($field, $title = '', $type = '', $isMandatory = false)
    {
        if (is_string($field)) {
            $this->fields[] = Field::make($field, $title, $type, $isMandatory);
            return;
        }

        $this->fields[] = $field;
    }

    public function addGroup(Schema $schema)
    {
        $this->groups[] = $schema;
    }

    public function merge(Schema $schema)
    {
        $this->fields = array_merge($this->fields, $schema->fields);
        $this->processors = array_merge($this->processors, $schema->processors);
    }

    /**
     * Удаляет из схемы все поля с ключом $key
     * @param string $key
     * @return $this
     */
    public function forgetField($key)
    {
        foreach ($this->fields as $k => $field) {
            if ($field->getKey() == $key) {
                unset($this->fields[$k]);
            }
        }
        $this->fields = array_values($this->fields);

        foreach ($this->getInnerSchemas() as $group) {
            $group->forgetField($key);
        }

        return $this;
    }

    /**
     * Возвращяет все ключи полей схемы
     * @return array
     */
    public function getKeys()
    {
        $keys = array();
        foreach ($this->fields as $k => $field) {
            $keys[] = $field->getKey();
        }
        foreach ($this->getInnerSchemas() as $group) {
            $keys = array_merge($keys, $group->getKeys());
        }
        return $keys;
    }

    /**
     * Возвращает все ключи полей схемы
     * @return array
     * @deprecated
     */
    public function getFields()
    {
        return $this->getKeys();
    }

    /**
     * Возвращает все наименования полей схемы
     * @return array
     */
    public function getTitles()
    {
        $titles = [];
        foreach ($this as $field) {
            $titles[] = $field->getTitle();
        }
        foreach ($this->getInnerSchemas() as $group) {
            $titles = array_merge($titles, $group->getTitles());
        }
        return $titles;
    }

    /**
     * Возвращает все типы полей схемы
     * @return array
     */
    public function getTypes()
    {
        $types = [];
        foreach ($this as $field) {
            $types[] = $field->getType();
        }
        foreach ($this->getInnerSchemas() as $group) {
            $types = array_merge($types, $group->getTypes());
        }
        return $types;
    }

    /**
     * Adds a processor on to the stack.
     *
     * @param  callable $callback
     * @return $this
     */
    public function pushProcessor($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('Processors must be valid callables (callback or object with an __invoke method), ' . var_export($callback, true) . ' given');
        }
        array_unshift($this->processors, $callback);

        return $this;
    }

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @return callable
     */
    public function popProcessor()
    {
        if (!$this->processors) {
            throw new LogicException('You tried to pop from an empty processor stack.');
        }

        return array_shift($this->processors);
    }

    /**
     * @return callable[]
     */
    public function getProcessors()
    {
        return $this->processors;
    }

    /**
     *
     * @param mixed $line
     * @return mixed
     */
    public function process($line)
    {
        foreach ($this->processors as $p) {
            $line = $p($line);
        }
        foreach ($this->getInnerSchemas() as $group) {
            $line = $group->process($line);
        }
        return $line;
    }

    /**
     *
     * @param array $data
     * @return array
     */
    public function processList($data)
    {
        foreach ($data as $k => $line) {
            $data[$k] = $this->process($line);
        }

        foreach ($this->getInnerSchemas() as $group) {
            $data = $group->processList($data);
        }
        return $data;
    }

    /**
     * Превращает ассоциативный массив с данными выборки из БД
     * в обычный массив без ключей
     * в соответствие со схемой в порядке следования полей схемы
     * @param array $line
     * @return array
     */
    public function makeFlatLine($line)
    {
        $newLine = [];
        foreach ($this->fields as $field) {
            $newLine[] = $field->draw($line);
        }
        foreach ($this->getInnerSchemas() as $group) {
            $newLine = array_merge($newLine, $group->makeFlatLine($line));
        }
        return $newLine;
    }

    /**
     * Превращает двумерный ассоциативный массив с выборкой из БД
     * в двумерный массив без ключей
     * в соответствие со схемой в порядке следования полей схемы
     * @param array $table
     * @return array
     */
    public function makeFlatTable(&$table)
    {
        $flat = [];
        foreach ($table as $v) {
            $flat[] = $this->makeFlatLine($v);
        }
        return $flat;
    }

    /**
     * Итератор по полям схемы
     * @return Field[]
     */
    public function getIterator()
    {
        $fields = $this->fields;
        foreach ($this->getInnerSchemas() as $group) {
            $fields = array_merge($fields, $group->fields);
        }
        return new ArrayIterator($fields);
    }

    /**
     * Итератор по группам
     * @return Schema[]
     */
    public function getGroupIterator()
    {
        $schemas = array_merge([$this->getMainSchema()], $this->getInnerSchemas());
        return new ArrayIterator($schemas);
    }

    protected function getMainSchema()
    {
        $schema = new Schema();
        $schema->title = $this->title;
        $schema->fields = $this->fields;
        $schema->processors = $this->processors;
        return $schema;
    }

    protected function getInnerSchemas()
    {
        $schemas = [];
        foreach ($this->groups as $group) {
            $schemas[] = $group->getMainSchema();
            $schemas = array_merge($schemas, $group->getInnerSchemas());
        }
        return $schemas;
    }

    /**
     *
     * @param array $data
     * @return array
     */
    public function validate($data)
    {
        $errors = [];
        $record = [];

        foreach ($this->getIterator() as $k => $field) {

            $path = str_replace(['[', ']'], ['.', ''], $field->getKey());
            $value = Arr::get($data, $path, null);

            if (empty($value) && $field->isMandatory()) {
                $errors[] = [__('Укажите значение'), $field->getKey()];
            }

            $error = App::type($field->getType())->setContext($data)->validate($value);
            if (!is_null($error)) {
                $errors[] = [$error, $field->getKey()];
            }

            if ($path) {
                Arr::set($record, $path, $value);
            }
        }

        if (!empty($errors)) {
            $e = new BadRequestException();
            $e->setErrors($errors);
            throw $e;
        }

        return $record;
    }

}
