<?php

namespace Pina\Data;

use Pina\App;
use Pina\Types\TypeInterface;

use function array_filter;
use function implode;

class Field
{

    protected $key = '';
    protected $title = '';
    protected $description = '';
    protected $type = '';
    protected $default = null;
    protected $isMandatory = false;
    protected $isNullable = false;

    //TODO: временный атрибут, по идее должно управляться через тип отношений (one-to-one, one-to-many, many-to-many)
    protected $isMultiple = false;

    /**
     * Создает экземпляр поля
     * @param string $key
     * @param string $title
     * @param mixed $type
     * @param boolean $isMandatory @deprecated
     * @param mixed $default @deprecated
     * @return \static
     */
    public static function make($key, $title, $type = 'string')
    {
        $field = new static;
        $field->key = $key;
        $field->title = $title;
        $field->type = $type;
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

    public function setMandatory($mandatory = true)
    {
        $this->isMandatory = $mandatory;
        return $this;
    }

    public function setNullable($nullable = true, $default = null)
    {
        $this->isNullable = $nullable;
        $this->default = $default;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return boolean
     * TODO: временный метод, по идее должно управляться через тип отношений (one-to-one, one-to-many, many-to-many)
     */
    public function isMultiple()
    {
        return $this->isMultiple;
    }

    /**
     * TODO: временный метод, по идее должно управляться через тип отношений (one-to-one, one-to-many, many-to-many)
     */
    public function setMultiple($multiple = true)
    {
        $this->isMultiple = $multiple;
        return $this;
    }

    public function isNullable()
    {
        return $this->isNullable || App::type($this->type)->isNullable();
    }

    private function isNullableForced()
    {
        return $this->isNullable && !App::type($this->type)->isNullable();
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        if (!is_null($this->default)) {
            return $this->default;
        } elseif ($this->isNullableForced()) {
            //если у поля насильно выставлен nullable,
            // то берем значение по умолчанию из поля, а не из типа
            return $this->default;
        }
        return App::type($this->type)->getDefault();
    }

    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    public function makeSQLDeclaration($definitions)
    {
        $type = App::type($this->type);
        $default = $this->getFormattedDefault($type);
        if (in_array('AUTO_INCREMENT', $definitions)) {
            $default = 'AUTO_INCREMENT';
        }
        return implode(
            ' ',
            array_filter(
                [$type->getSQLType(), $this->isNullable() ? "" : "NOT NULL", $default]
            )
        );
    }

    /**
     * @param TypeInterface $type
     * @return mixed|string|null
     */
    private function getFormattedDefault($type)
    {
        $default = $this->getDefault();
        if (is_null($default)) {
            if ($this->isNullable()) {
                return 'DEFAULT NULL';
            } else {
                return '';
            }
        }

        if ($default == 'CURRENT_TIMESTAMP') {
            return 'DEFAULT ' . $default;
        }

        if (is_string($default)) {
            return "DEFAULT '" . $default . "'";
        }

        return 'DEFAULT ' . $default;
    }

}