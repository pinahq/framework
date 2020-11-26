<?php

namespace Pina\Components;

class Schema implements \Iterator
{

    protected $fields = [];
    protected $cursor = 0;

    /**
     * Добавляет в схему поле
     * @param mixed $field
     * @param string $title
     * @param string $type
     * @return void
     */
    public function add($field, $title = '', $type = '')
    {
        if (is_string($field)) {
            $this->fields[] = Field::make($field, $title, $type);
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
     * Поддержка Iterable
     * @return \Pina\Components\Field
     */
    public function current()
    {
        return $this->fields[$this->cursor];
    }

    public function key()
    {
        return $this->cursor;
    }

    public function next()
    {
        $this->cursor ++;
    }

    public function rewind()
    {
        $this->cursor = 0;
    }

    public function valid()
    {
        return isset($this->fields[$this->cursor]);
    }

}
