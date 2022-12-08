<?php


namespace Pina\Controls;


abstract class FormControl extends Control
{
    abstract public function setTitle(string $title): FormControl;

    abstract public function setName(string $name): FormControl;

    abstract public function setValue(?string $value): FormControl;

    abstract public function setDescription(string $description): FormControl;

    abstract public function setRequired(bool $required = true): FormControl;

    abstract public function setCompact(bool $compact = true): FormControl;

}