<?php

namespace Pina\Data;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use LogicException;
use Pina\App;
use Pina\Arr;
use Pina\BadRequestException;
use Pina\Container\NotFoundException;
use Pina\Types\IntegerType;
use Pina\Types\StringType;
use Pina\Types\TimestampType;
use Pina\Types\ValidateException;

class Schema implements IteratorAggregate
{

    protected $title = '';

    protected $description = '';

    /**
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
     * @var \Pina\Data\Schema[]
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
    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Добавляет в схему поле
     * @param mixed $field
     * @param string $title
     * @param string $type
     * @throws NotFoundException
     * @return Field
     */
    public function add($field, $title = '', $type = ''): Field
    {
        if (empty($type)) {
            //TODO: убрать значения title и type по умолчанию
            $type = StringType::class;
        }
        if (is_string($field)) {
            $f = Field::make($field, $title, $type);
            $this->fields[] = $f;
            return $f;
        }

        $this->fields[] = $field;
        return $field;
    }

    public function addField(Field $field): Field
    {
        $this->fields[] = $field;
        return $field;
    }

    /**
     * Добавить вложенную схему
     * @param Schema $schema
     */
    public function addGroup(Schema $schema)
    {
        $this->groups[] = $schema;
    }

    /**
     * Добавляет в схему поля created_at и updated_at
     * @param string $createdAtTitle
     * @param string $updatedAtTitle
     * @throws \Exception
     */
    public function addTimestamps($createdAtTitle = 'Created', $updatedAtTitle = 'Updated')
    {
        $this->addCreatedAt($createdAtTitle);
        $this->addUpdatedAt($updatedAtTitle);
    }

    /**
     * Добавляет в схему поле created_at
     * @param string $title
     * @throws \Exception
     * @return Field
     */
    public function addCreatedAt($title = 'Created'): Field
    {
        return $this->add('created_at', $title, TimestampType::class)
            ->setStatic()
            ->setNullable(false)
            ->setDefault('CURRENT_TIMESTAMP');
    }

    /**
     * Добавляет в схему поле updated_at
     * @param string $title
     * @throws \Exception
     * @return Field
     */
    public function addUpdatedAt($title = 'Updated'): Field
    {
        $field = $this->add('updated_at', $title, TimestampType::class)
            ->setStatic()
            ->setNullable(false)
            ->setDefault('CURRENT_TIMESTAMP');

        $this->addFieldDefinition('updated_at', 'ON UPDATE CURRENT_TIMESTAMP');
        return $field;
    }

    /**
     * Объединить (слить поля и процессоры) с другой схемой
     * TODO: выработать стратегию слияния индексов, уникальных и главных ключей
     * @param Schema $schema
     */
    public function merge(Schema $schema)
    {
        $this->fields = array_merge($this->fields, $schema->fields);
        $this->dataProcessors = array_merge($this->dataProcessors, $schema->dataProcessors);
        $this->textProcessors = array_merge($this->textProcessors, $schema->textProcessors);
        $this->htmlProcessors = array_merge($this->htmlProcessors, $schema->htmlProcessors);

        if (empty($this->primaryKey) && !empty($schema->primaryKey)) {
            $this->primaryKey = $schema->primaryKey;
        }
    }

    public function setMandatory($mandatory = true)
    {
        foreach ($this->fields as $field) {
            $field->setMandatory($mandatory);
        }
        foreach ($this->getInnerSchemas() as $group) {
            $group->setMandatory($mandatory);
        }
        return $this;
    }

    public function setNullable($nullable = true, $default = null)
    {
        foreach ($this->fields as $field) {
            $field->setNullable($nullable, $default);
        }
        foreach ($this->getInnerSchemas() as $group) {
            $group->setNullable($nullable, $default);
        }
        return $this;
    }

    public function setStatic($static = true)
    {
        foreach ($this->fields as $field) {
            $field->setStatic($static);
        }
        foreach ($this->getInnerSchemas() as $group) {
            $group->setStatic($static);
        }
        return $this;
    }

    /**
     * Удаляет из схемы все поля с ключом $key
     * @param string $key
     * @return $this
     */
    public function forgetField(string $key)
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
     * Удаляет из схемы все поля с указанными ключам $keys
     * @param string[] $keys
     * @return $this
     */
    public function forgetFields(array $keys)
    {
        foreach ($this->fields as $k => $field) {
            if (in_array($field->getKey(), $keys)) {
                unset($this->fields[$k]);
            }
        }
        $this->fields = array_values($this->fields);

        foreach ($this->getInnerSchemas() as $group) {
            $group->forgetFields($keys);
        }

        return $this;
    }

    /**
     * Удаляет из схемы все статические поля
     * @return $this
     */
    public function forgetStatic()
    {
        foreach ($this->fields as $k => $field) {
            if ($field->isStatic()) {
                unset($this->fields[$k]);
            }
        }
        $this->fields = array_values($this->fields);

        foreach ($this->getInnerSchemas() as $group) {
            $group->forgetStatic();
        }

        return $this;
    }

    public function forgetNotFiltrable()
    {
        foreach ($this->fields as $k => $field) {
            if (!$field->isFiltrable()) {
                unset($this->fields[$k]);
            }
        }

        $this->fields = array_values($this->fields);

        foreach ($this->getInnerSchemas() as $group) {
            $group->forgetNotFiltrable();
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

    public function isEditable()
    {
        foreach ($this->fields as $field) {
            if (!$field->isStatic()) {
                return true;
            }
        }

        foreach ($this->getInnerSchemas() as $group) {
            if ($group->isEditable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Возвращяет все ключи полей схемы
     * @return array
     */
    public function getFieldKeys()
    {
        $keys = array();
        foreach ($this->fields as $field) {
            $keys[] = $field->getKey();
        }
        foreach ($this->getInnerSchemas() as $group) {
            $keys = array_merge($keys, $group->getFieldKeys());
        }
        return $keys;
    }


    /**
     * Возвращает все наименования полей схемы
     * @return array
     */
    public function getFieldTitles()
    {
        $titles = [];
        foreach ($this->fields as $field) {
            $titles[] = $field->getTitle();
        }
        foreach ($this->getInnerSchemas() as $group) {
            $titles = array_merge($titles, $group->getFieldTitles());
        }
        return $titles;
    }

    /**
     * Возвращает все типы полей схемы
     * @return array
     */
    public function getFieldTypes()
    {
        $types = [];
        foreach ($this->fields as $field) {
            $types[] = $field->getType();
        }
        foreach ($this->getInnerSchemas() as $group) {
            $types = array_merge($types, $group->getFieldTypes());
        }
        return $types;
    }

    /**
     * Добавляет мета-процессор в стек процессоров.
     * Мета-процессор должен быть функцией с двумя параметрами:
     * 1. Ассоциативный массив с meta-данными, полученными с прошлого этапа процессинга
     * 2. Ассоциативный массив с оригинальной выборкой для анализа
     * Мета-процессор должен дополнить и вернуть массив с метаданными
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
     * Удаляет очередной мета-процессор из стека процессоров
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
     * Возвращает список мета-процессоров
     * @return callable[]
     */
    public function getMetaProcessors()
    {
        return $this->metaProcessors;
    }


    /**
     * Добавляет процессор данных в стек процессоров.
     * Дата-процессор должен быть функцией с одним параметром:
     * 1. Ассоциативный массив с данными, полученными с прошлого этапа процессинга
     * Мета-процессор должен дополнить и вернуть массив с данными
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
     * Удаляет очередной процессор данных из стека процессоров
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
     * Возвращает список процессоров данных
     * @return callable[]
     */
    public function getDataProcessors()
    {
        return $this->dataProcessors;
    }

    /**
     * Добавляет текстовый процессор в стек процессоров.
     * Текстовый процессор форматирует данные для отрисовки в тексте
     * и должен быть функцией с двумя параметрами:
     * 1. Ассоциативный массив с текстовыми-данными, полученными с прошлого этапа процессинга
     * 2. Ассоциативный массив с оригинальной выборкой для анализа
     * Текстовый процессор должен дополнить и вернуть массив с текстовыми данными
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
     * Удаляет очередной текстовый процессор из стека процессоров
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
     * Возвращает список текстовых процессоров
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
        return $this->callMetaProcessors([], $line);
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

    /**
     * @param array $line
     * @return array
     * @throws \Exception
     */
    public function processLineAsText($line)
    {
        $processed = $this->processLineAsData($line);
        $formatted = [];
        foreach ($this->getIterator() as $field) {
            if ($field->isHidden()) {
                continue;
            }
            $key = $field->getKey();
            $value = (!isset($processed[$key]) || $processed[$key] == '') ? $field->getDefault() : $processed[$key];
            $type = App::type($field->getType());
            $formatted[$key] = $type->format($value);
        }
        return $this->makeLine($this->callTextProcessors($formatted, $line));
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function processListAsText($data)
    {
        foreach ($data as $k => $line) {
            $data[$k] = $this->processLineAsText($line);
        }
        return $data;
    }

    /**
     * @param array $line
     * @return array
     * @throws \Exception
     */
    public function processLineAsHtml($line)
    {
        $processed = $this->processLineAsData($line);
        $formatted = [];
        foreach ($this->getIterator() as $field) {
            if ($field->isHidden()) {
                continue;
            }
            $key = $field->getKey();
            $value = (!isset($processed[$key]) || $processed[$key] == '') ? $field->getDefault() : $processed[$key];
            $type = App::type($field->getType());
            $type->setContext($line);
            $formatted[$key] = $type->draw($value);
        }
        return $this->makeLine($this->callHtmlProcessors($formatted, $line));
    }

    /**
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function processListAsHtml($data)
    {
        foreach ($data as $k => $line) {
            $data[$k] = $this->processLineAsHtml($line);
        }
        return $data;
    }


    public function makeLine($line)
    {
        $newLine = [];
        foreach ($this->getIterator() as $field) {
            if ($field->isHidden()) {
                continue;
            }
            $key = $field->getKey();
            $newLine[$key] = isset($line[$key]) ? $line[$key] : '';
        }
        return $newLine;
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
            if ($field->isHidden()) {
                continue;
            }
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
        $schema = clone $this;
        $schema->groups = [];
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
     * @throws \Exception
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

    public function has(string $fieldKey)
    {
        foreach ($this->getIterator() as $field) {
            if ($field->getKey() == $fieldKey) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string[] $fieldKeys
     */
    public function fieldset($fieldKeys): FieldSet
    {
        $fieldset = new FieldSet($this);
        foreach ($fieldKeys as $fieldKey) {
            $fieldset->select($fieldKey);
        }
        return $fieldset;
    }

    /**
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function normalize($data)
    {
        $errors = [];
        $record = [];

        foreach ($this->getIterator() as $field) {
            if ($field->isStatic()) {
                continue;
            }

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

    /**
     * Дополняет данные данными из внешних источников
     * @param $data
     * @throws \Exception
     */
    public function fill(&$data)
    {
        $pk = $this->getPrimaryKey();
        if (empty($pk)) {
            return;
        }
        if (count($pk) > 1) {
            return;
        }
        $primaryKey = array_shift($pk);
        if (!isset($data[$primaryKey])) {
            return;
        }
        $id = $data[$primaryKey];
        foreach ($this->getIterator() as $field) {
            if ($field->isStatic()) {
                continue;
            }
            $path = str_replace(['[', ']'], ['.', ''], $field->getKey());
            $value = Arr::get($data, $path, null);
            if (is_null($value)) {
                Arr::set($data, $path, App::type($field->getType())->getData($id));
            }
        }
    }

    /**
     * Синхронизирует данные во внешних источниках
     * @param $id
     * @param $data
     * @throws \Exception
     */
    public function onUpdate($id, $data)
    {
        foreach ($this->getIterator() as $field) {
            if ($field->isStatic()) {
                continue;
            }
            $path = str_replace(['[', ']'], ['.', ''], $field->getKey());
            $value = Arr::get($data, $path, null);

            App::type($field->getType())->setData($id, $value);
        }
    }

    /**
     * @param array $fields
     * @return array
     * @throws \Exception
     */
    public function makeSQLFields($fields = [])
    {
        foreach ($this->getIterator() as $field) {
            /** @var Field $field */
            $key = $field->getKey();
            if (isset($fields[$key])) {
                continue;
            }
            $declaration = $field->makeSQLDeclaration($this->definitions[$key] ?? []);
            if (empty($declaration)) {
                continue;
            }
            $fields[$key] = $declaration;
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

    public function restrictPrimaryKeyContext(array $fields)
    {
        $this->primaryKey = array_values(array_diff($this->primaryKey, $fields));
    }

    /**
     * Добавляет в схему целочисленный PK с автоинкрементом
     * @param $field
     * @param $title
     * @return Field
     * @throws \Exception
     */
    public function addAutoincrementPrimaryKey($field, $title)
    {
        $r = $this->add($field, $title, IntegerType::class)->setStatic();
        $this->setPrimaryKey([$field]);
        $this->addFieldDefinition($field, 'AUTO_INCREMENT');
        return $r;
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
     * @return \string[][]
     */
    public function getUniqueKeys()
    {
        $keys = $this->uniqueKeys;
        foreach ($this->getInnerSchemas() as $group) {
            $keys = array_merge($keys, $group->getUniqueKeys());
        }
        return $keys;
    }

    /**
     * @param array|string $fields
     */
    public function addKey($fields)
    {
        $this->keys[] = is_array($fields) ? $fields : func_get_args();
    }

    /**
     * @param string $fieldKey
     * @param string $definition
     */
    public function addFieldDefinition($fieldKey, $definition)
    {
        if (!isset($this->definitions[$fieldKey])) {
            $this->definitions[$fieldKey] = [];
        }
        $this->definitions[$fieldKey][] = $definition;
    }

    protected function callMetaProcessors($processed, $raw)
    {
        foreach ($this->metaProcessors as $f) {
            $processed = $f($processed, $raw);
        }
        foreach ($this->getInnerSchemas() as $group) {
            $processed = $group->callMetaProcessors($processed, $raw);
        }
        return $processed;
    }

    protected function callTextProcessors($processed, $raw)
    {
        foreach ($this->textProcessors as $f) {
            $processed = $f($processed, $raw);
        }
        foreach ($this->getInnerSchemas() as $group) {
            $processed = $group->callTextProcessors($processed, $raw);
        }
        return $processed;
    }

    protected function callHtmlProcessors($processed, $raw)
    {
        foreach ($this->textProcessors as $f) {
            $processed = $f($processed, $raw);
        }
        foreach ($this->htmlProcessors as $f) {
            $processed = $f($processed, $raw);
        }
        foreach ($this->getInnerSchemas() as $group) {
            $processed = $group->callHtmlProcessors($processed, $raw);
        }
        return $processed;
    }

}