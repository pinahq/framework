<?php

namespace Pina\Components;

use Pina\App;

class Field
{

    protected $key = '';
    protected $title = '';
    protected $type = '';
    protected $isMandatory = '';

    /**
     * Создает экземпляр поля
     * @param string $key
     * @param string $title
     * @param mixed $type
     * @param boolean $isMandatory
     * @return \static
     */
    public static function make($key, $title, $type = 'string', $isMandatory = false)
    {
        $field = new static;
        $field->key = $key;
        $field->title = $title;
        $field->type = $type;
        $field->isMandatory = $isMandatory;
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
    
    public function isMandatory()
    {
        return $this->isMandatory;
    }

    /**
     * Отрисовать данные в поле в соответствие с настройками поля 
     * @param array $line
     * @return string
     */
    public function draw($line)
    {
        if (!isset($line[$this->key]) || $line[$this->key] === '') {
            return App::type($this->getType())->getDefault();
        }

        return $line[$this->key];
    }

}
