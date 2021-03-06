<?php

namespace Pina\Components;

use Pina\App;

class Field
{

    protected $key = '';
    protected $title = '';
    protected $type = '';
    protected $isMandatory = '';
    protected $default = null;

    /**
     * Создает экземпляр поля
     * @param string $key
     * @param string $title
     * @param mixed $type
     * @param boolean $isMandatory
     * @param mixed $default
     * @return \static
     */
    public static function make($key, $title, $type = 'string', $isMandatory = false, $default = null)
    {
        $field = new static;
        $field->key = $key;
        $field->title = $title;
        $field->type = $type;
        $field->isMandatory = $isMandatory;
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
     * @return boolean
     */
    public function isMandatory()
    {
        return $this->isMandatory;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        if (!is_null($this->default)) {
            return $this->default;
        }
        return App::type($this->type)->getDefault();
    }

    /**
     * Отрисовать данные в поле в соответствие с настройками поля
     * @param array $line
     * @return string
     */
    public function draw($line)
    {
        if (!isset($line[$this->key]) || $line[$this->key] === '') {
            return $this->getDefault();
        }

        return $line[$this->key];
    }

    public function makeSQLDeclaration()
    {
        $type = App::type($this->type);
        return $type->getSQLType()
            . ($type->isNullable() ? "" : " NOT NULL")
            . " DEFAULT " . $this->getFormattedDefault();
    }

    private function getFormattedDefault()
    {
        $default = $this->getDefault();
        if (is_null($default)) {
            return 'NULL';
        }

        if ($default == 'CURRENT_TIMESTAMP') {
            return $default;
        }

        if (is_string($default)) {
            return "'" . $default . "'";
        }

        return $default;
    }

}
