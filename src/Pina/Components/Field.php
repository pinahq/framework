<?php

namespace Pina\Components;

class Field
{

    protected $key = '';
    protected $title = '';
    protected $type = '';
    protected $pattern = '';
    protected $default = '';

    /**
     * Создает экземпляр поля
     * @param type $key
     * @param type $title
     * @param type $type
     * @param type $default
     * @return \static
     */
    public static function make($key, $title, $type = '', $default = '')
    {
        $field = new static;
        $field->key = $key;
        $field->title = $title;
        $field->type = $type;
        $field->default = $default;
        return $field;
    }

    /**
     * Получает ключ поля
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Получить наименование поля
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Получить тип поля
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Получить значение по умолчанию
     * @return string
     */
    public function getDefault()
    {
        return $this->default;
    }
    
    /**
     * Настроить шаблон форматирования поля
     * @param string $pattern
     * @return $this
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
        return $this;
    }

    /**
     * Отисовать данные в поле в соответствие с настройками поля 
     * @param array $line
     * @return string
     */
    public function draw($line)
    {
        if (!isset($line[$this->key]) || $line[$this->key] === '') {
            return $this->getDefault();
        }
        
        if ($this->pattern) {
            return $this->drawPattern($line);
        }
        
        return $line[$this->key];
    }
    
    /**
     * Отрисовать данные в поле в соответствие с настроенным шаблоном
     * @param array $line
     * @return string
     */
    protected function drawPattern($line)
    {
        if (preg_match_all("/{([\w\d_-]+)}/si", $this->pattern, $matches)) {
            $names = array_pop($matches);
            $r = $this->pattern;
            foreach ($names as $name) {
                $r = str_replace('{'.$name.'}', isset($line[$name]) ? $line[$name] : '', $r);
            }
            return $r;
        }
        return $this->pattern;
    }
    

}
