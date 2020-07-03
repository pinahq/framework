<?php

namespace Pina\Components;

use \Pina\Container\NotFoundException;

class Registry
{

    static $components = [
        'table' => TableComponent::class,
        'row' => RowComponent::class,
        'list' => ListComponent::class,
    ];

    public static function register($id, $concrete)
    {
        static::$components[$id] = $concrete;
    }

    public static function get($id)
    {
        if (!array_key_exists($id, static::$components)) {
            throw new NotFoundException(
            sprintf('Alias (%s) is not being managed by the container', $id)
            );
        }

        if (is_object(static::$components[$id])) {
            return clone(static::$components[$id]);
        }

        if (is_string(static::$components[$id])) {
            $className = static::$components[$id];
            if (class_exists($className)) {
                return new $className;
            }

            throw new NotFoundException(
            sprintf('Unable to create alias (%s) since class (%s) does not exists', $id, $className)
            );
        }

        throw new NotFoundException(
        sprintf('Unable to create alias (%s) as it does not have appropriate type', $id)
        );
    }

    public static function has($id)
    {
        return array_key_exists($id, static::$components);
    }

}
