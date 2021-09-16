<?php

namespace Pina\Data;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use LogicException;
use Pina\App;
use Pina\Arr;
use Pina\BadRequestException;
use Pina\Types\ValidateException;

class Schema implements IteratorAggregate
{

    protected $title = '';

    /**
     *
     * @var Field[]
     */
    protected $fields = [];

    /**
     * @var callable[]
     */
    protected $metaProcessors = [];

    /**
     * @var callable[]
     */
    protected $dataProcessors = [];

    /**
     * @var callable[]
     */
    protected $textProcessors = [];

    /**
     * @var callable[]
     */
    protected $htmlProcessors = [];

    /**
     *
     * @var \Pina\Components\Schema[]
     */
    protected $groups = [];

    /**
     * @var string[]
     */
    protected $primaryKey = [];

    /**
     * @var string[][]
     */
    protected $uniqueKeys = [];

    /**
     * @var string[][]
     */
    protected $keys = [];

    /**
     * @var array
     */
    protected $definitions = [];

    /**
     * @param $title
     * @return $this
     */
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
     * @param mixed $default
     * @return Field
     */
    public function add($field, $title = '', $type = '', $isMandatory = false, $default = null)
    {
        if (is_string($field)) {
            $f = Field::make($field, $title, $type, $isMandatory, $default);
            $this->fields[] = $f;
            return $f;
        }

        $this->fields[] = $field;
        return $field;
    }

    public function addGroup(Schema $schema)
    {
        $this->groups[] = $schema;
    }

    public function merge(Schema $schema)
    {
        $this->fields = array_merge($this->fields, $schema->fields);
        $this->dataProcessors = array_merge($this->dataProcessors, $schema->dataProcessors);
        $this->textProcessors = array_merge($this->textProcessors, $schema->textProcessors);
        $this->htmlProcessors = array_merge($this->htmlProcessors, $schema->htmlProcessors);
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

    public function getVolume()
    {
        $count = count($this->fields);
        foreach ($this->getInnerSchemas() as $group) {
            $count += $group->getVolume();
        }
        return $count;
    }

    public function isEmpty()
    {
        return $this->getVolume() == 0;
    }

    /**
     * Возвращяет все ключи полей схемы
     * @return array
     */
    public function getKeys()
    {
        $keys = array();
        foreach ($this->fields as $field) {
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
        foreach ($this->fields as $field) {
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
        foreach ($this->fields as $field) {
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
     * @param callable $callback
     * @return $this
     */
    public function pushMetaProcessor($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException(
                'Processors must be valid callables (callback or object with an __invoke method), ' . var_export(
                    $callback,
                    true
                ) . ' given'
            );
        }
        array_push($this->metaProcessors, $callback);

        return $this;
    }

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @return callable
     */
    public function popMetaProcessor()
    {
        if (!$this->metaProcessors) {
            throw new LogicException('You tried to pop from an empty processor stack.');
        }

        return array_pop($this->metaProcessors);
    }


    /**
     * Adds a processor on to the stack.
     *
     * @param callable $callback
     * @return $this
     */
    public function pushDataProcessor($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException(
                'Processors must be valid callables (callback or object with an __invoke method), ' . var_export(
                    $callback,
                    true
                ) . ' given'
            );
        }
        array_push($this->dataProcessors, $callback);

        return $this;
    }

    /**
     * Removes the processor on top of the stack and returns it.
     *
     * @return callable
     */
    public function popDataProcessor()
    {
        if (!$this->dataProcessors) {
            throw new LogicException('You tried to pop from an empty processor stack.');
        }

        return array_pop($this->dataProcessors);
    }

    /**
     * @return callable[]
     */
    public function getDataProcessors()
    {
        return $this->dataProcessors;
    }

    /**
     * Adds a formatter on to the stack.
     *
     * @param callable $callback
     * @return $this
     */
    public function pushTextProcessor($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException(
                'Processors must be valid callables (callback or object with an __invoke method), ' . var_export(
                    $callback,
                    true
                ) . ' given'
            );
        }
        array_push($this->textProcessors, $callback);

        return $this;
    }

    /**
     * Removes the formatter on top of the stack and returns it.
     *
     * @return callable
     */
    public function popTextProcessor()
    {
        if (!$this->textProcessors) {
            throw new LogicException('You tried to pop from an empty text processor stack.');
        }

        return array_pop($this->textProcessors);
    }

    /**
     * @return callable[]
     */
    public function getTextProcessors()
    {
        return $this->textProcessors;
    }


    /**
     * Adds a designer on to the stack.
     *
     * @param callable $callback
     * @return $this
     */
    public function pushHtmlProcessor($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException(
                'Processors must be valid callables (callback or object with an __invoke method), ' . var_export(
                    $callback,
                    true
                ) . ' given'
            );
        }
        array_push($this->htmlProcessors, $callback);

        return $this;
    }

    /**
     * Removes the html processor on top of the stack and returns it.
     *
     * @return callable
     */
    public function popHtmlProcessor()
    {
        if (!$this->htmlProcessors) {
            throw new LogicException('You tried to pop from an empty html processor stack.');
        }

        return array_pop($this->htmlProcessors);
    }

    /**
     * @return callable[]
     */
    public function getHtmlProcessors()
    {
        return $this->htmlProcessors;
    }


    /**
     *
     * @param mixed $line
     * @return mixed
     */
    public function processLineAsMeta($line)
    {
        $meta = [];
        foreach ($this->metaProcessors as $p) {
            $meta = array_merge($meta, $p($line));
        }
        foreach ($this->getInnerSchemas() as $group) {
            $meta = array_merge($meta, $group->processLineAsMeta($line));
        }
        return $meta;
    }

    /**
     *
     * @param mixed $line
     * @return mixed
     */
    public function processLineAsData($line)
    {
        foreach ($this->dataProcessors as $p) {
            $line = $p($line);
        }
        foreach ($this->getInnerSchemas() as $group) {
            $line = $group->processLineAsData($line);
        }
        return $line;
    }

    /**
     *
     * @param array $data
     * @return array
     */
    public function processListAsData($data)
    {
        foreach ($data as $k => $line) {
            $data[$k] = $this->processLineAsData($line);
        }
        return $data;
    }

    public function callTextProcessors($processed, $raw)
    {
        foreach ($this->textProcessors as $f) {
            $processed = $f($processed, $raw);
        }
        foreach ($this->getInnerSchemas() as $group) {
            $processed = $group->callTextProcessors($processed, $raw);
        }
        return $processed;
    }

    public function processLineAsText($line)
    {
        $processed = $this->processLineAsData($line);
        $formatted = [];
        foreach ($this->getIterator() as $field) {
            $key = $field->getKey();
            $value = (!isset($processed[$key]) || $processed[$key] == '') ? $field->getDefault() : $processed[$key];
            $type = App::type($field->getType());
            $formatted[$key] = $type->format($value);
        }
        return $this->callTextProcessors($formatted, $line);
    }

    public function processListAsText($data)
    {
        foreach ($data as $k => $line) {
            $data[$k] = $this->processLineAsText($line);
        }
        return $data;
    }

    public function processLineAsHtml($line)
    {
        $processed = $this->processLineAsText($line);
        return $this->callHtmlProcessors($processed, $line);
    }

    public function processListAsHtml($data)
    {
        foreach ($data as $k => $line) {
            $data[$k] = $this->processLineAsHtml($line);
        }
        return $data;
    }

    public function callHtmlProcessors($processed, $raw)
    {
        foreach ($this->htmlProcessors as $f) {
            $processed = $f($processed, $raw);
        }
        foreach ($this->getInnerSchemas() as $group) {
            $processed = $group->callHtmlProcessors($processed, $raw);
        }
        return $processed;
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
        foreach ($this->getIterator() as $field) {
            $key = $field->getKey();
            $newLine[] = isset($line[$key]) ? $line[$key] : '';
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
        $schema->dataProcessors = $this->dataProcessors;
        $schema->textProcessors = $this->textProcessors;
        $schema->htmlProcessors = $this->htmlProcessors;
        return $schema;
    }

    /**
     * @return Schema[]
     */
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
     * @param string[] $fieldKeys
     */
    public function only($fieldKeys)
    {
        $schema = new Schema();
        foreach ($fieldKeys as $fieldKey) {
            foreach ($this->getIterator() as $field) {
                if ($field->getKey() == $fieldKey) {
                    $schema->add(clone $field);
                }
            }
        }
        return $schema;
    }

    /**
     *
     * @param array $data
     * @return array
     */
    public function normalize($data)
    {
        $errors = [];
        $record = [];

        foreach ($this->getIterator() as $field) {
            $path = str_replace(['[', ']'], ['.', ''], $field->getKey());
            $value = Arr::get($data, $path, null);

            try {
                if ($value == '' && $field->isNullable() && !$field->isMandatory()) {
                    $value = null;
                } else {
                    $value = App::type($field->getType())->setContext($data)->normalize($value, $field->isMandatory());
                }
            } catch (ValidateException $e) {
                $errors[] = [$e->getMessage(), $field->getKey()];
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

    public function makeSQLFields($fields = [])
    {
        foreach ($this->getIterator() as $field) {
            /** @var Field $field */
            $key = $field->getKey();
            if (isset($fields[$key])) {
                continue;
            }
            $fields[$key] = $field->makeSQLDeclaration($this->definitions[$key] ?? []);
        }
        return $fields;
    }

    public function makeSQLIndexes($indexes = [])
    {
        if ($this->primaryKey) {
            if (!isset($indexes['PRIMARY KEY'])) {
                $indexes['PRIMARY KEY'] = $this->primaryKey;
            }
        }
        foreach ($this->uniqueKeys as $key) {
            $name = 'UNIQUE KEY unique_' . implode('_', $key);
            if (!isset($indexes[$name])) {
                $indexes[$name] = $key;
            }
        }
        foreach ($this->keys as $key) {
            $name = 'KEY key_' . implode('_', $key);
            if (!isset($indexes[$name])) {
                $indexes[$name] = $key;
            }
        }

        return $indexes;
    }

    /**
     * @param array|string $fields
     */
    public function setPrimaryKey($fields)
    {
        $this->primaryKey = is_array($fields) ? $fields : func_get_args();
    }

    /**
     * @return string[]
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param array|string $fields
     */
    public function addUniqueKey($fields)
    {
        $this->uniqueKeys[] = is_array($fields) ? $fields : func_get_args();
    }

    /**
     * @param array|string $fields
     */
    public function addKey($fields)
    {
        $this->keys[] = is_array($fields) ? $fields : func_get_args();
    }

    /**
     * @param string $field
     * @param string $definition
     */
    public function addFieldDefinition($field, $definition)
    {
        if (!isset($this->definitions[$field])) {
            $this->definitions[$field] = [];
        }
        $this->definitions[$field][] = $definition;
    }

}
