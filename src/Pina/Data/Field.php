<?php

namespace Pina\Data;

use Pina\App;

use Pina\Container\NotFoundException;
use function array_filter;
use function implode;

class Field
{

    protected $key = '';
    protected $alias = null;
    protected $title = '';
    protected $description = '';
    protected $type = '';
    protected $default = null;
    protected $isMandatory = false;
    protected $isNullable = false;
    protected $isStatic = false;
    protected $isHidden = false;
    protected $width = 12;

    //TODO: временный атрибут, по идее должно управляться через тип отношений (one-to-one, one-to-many, many-to-many)
    protected $isMultiple = false;

    /**
     * Создает экземпляр поля
     * @param string $key
     * @param string $title
     * @param mixed $type
     * @param boolean $isMandatory @deprecated
     * @param mixed $default @deprecated
     * @throws NotFoundException
     * @return \static
     */
    public static function make($key, $title, $type)
    {
        $field = new static;
        $field->key = $key;
        $field->title = $title;
        $field->type = $type;
        $field->isNullable = App::type($type)->isNullable();
        return $field;
    }

    /**
     * Получает ключ поля
     * @return string
     */
    public function getKey()
    {
        return $this->alias ?? $this->key;
    }

    public function setAlias(string $alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Получить наименование поля
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Получить тип поля
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function setWidth(int $width)
    {
        $this->width = $width;
        return $this;
    }

    public function getWidth()
    {
        return $this->width;
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

    public function setStatic($static = true)
    {
        $this->isStatic = $static;
        return $this;
    }

    public function isStatic()
    {
        return $this->isStatic;
    }

    public function setHidden($hidden = true)
    {
        $this->isHidden = $hidden;
        return $this;
    }

    public function isHidden()
    {
        return $this->isHidden;
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

    /**
     * @return bool
     * @throws \Exception
     */
    public function isNullable()
    {
        return $this->isNullable;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function isNullableForced()
    {
        return $this->isNullable && !App::type($this->type)->isNullable();
    }

    /**
     * @return mixed
     * @throws \Exception
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

    /**
     * @param $definitions
     * @return string
     * @throws \Exception
     */
    public function makeSQLDeclaration($definitions)
    {
        $type = App::type($this->type);
        $sqlType = $type->getSQLType();
        if (empty($sqlType)) {
            return '';
        }
        $default = $this->getFormattedDefault();
        if (in_array('AUTO_INCREMENT', $definitions)) {
            $default = 'AUTO_INCREMENT';
        }
        if (in_array('ON UPDATE CURRENT_TIMESTAMP', $definitions)) {
            $default .= ' ON UPDATE CURRENT_TIMESTAMP';
        }
        return implode(
            ' ',
            array_filter(
                [$sqlType, $this->isNullable() ? "" : "NOT NULL", $default]
            )
        );
    }

    /**
     * @return mixed|string|null
     * @throws \Exception
     */
    private function getFormattedDefault()
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

    public function isFiltrable()
    {
        return App::type($this->type)->isFiltrable();
    }

}