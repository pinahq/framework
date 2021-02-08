<?php

namespace Pina\Components;

use Pina\Types\TypeInterface;
use Pina\App;
use Pina\Arr;
use function Pina\__;

class Schema implements \IteratorAggregate
{

    /**
     *
     * @var Field[]
     */
    protected $fields = [];
    protected $cursor = 0;
    protected $processors = [];

    /**
     * Добавляет в схему поле
     * @param mixed $field
     * @param string $title
     * @param string $type
     * @return void
     */
    public function add($field, $title = '', $type = '', $default = '')
    {
        if (is_string($field)) {
            $this->fields[] = Field::make($field, $title, $type, $default);
            return;
        }

        $this->fields[] = $field;
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
        return $keys;
    }

    /**
     * Возвращает все ключи полей схемы
     * @return arrar
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
            throw new \InvalidArgumentException('Processors must be valid callables (callback or object with an __invoke method), ' . var_export($callback, true) . ' given');
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
            throw new \LogicException('You tried to pop from an empty processor stack.');
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
        return new \ArrayIterator($this->fields);
    }
    
    public function validate($data)
    {
        $errors = [];
        $record = [];
        
        foreach ($this->fields as $k => $field) {
            
            $path = str_replace(['[', ']'], ['.', ''], $field->getKey());
            $value = Arr::get($data, $path, null);
            
            if (empty($value) && $field->isMandatory()) {
                $errors[] = [__('Укажите значение'), $field->getKey()];
            }
            
            $error = App::type($field->getType())->validate($value);
            if (!empty($error)) {
                $errors[] = [$error, $field->getKey()];
            }
            
            if ($path) {
                Arr::set($record, $path, $value);
            }
        }
        
        return [$errors, $record];
    }

}
