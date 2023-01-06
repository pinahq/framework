<?php


namespace Pina\Controls;


abstract class FormControl extends Control
{
    abstract public function setTitle(string $title): FormControl;

    abstract public function setName(string $name): FormControl;

    //не можем ограничить тип $value строкой, так как может быть, например, множественный выбор в select
    abstract public function setValue($value): FormControl;

    abstract public function setDescription(string $description): FormControl;

    abstract public function setRequired(bool $required = true): FormControl;

    abstract public function setCompact(bool $compact = true): FormControl;

}