<?php

namespace Pina;

interface ModuleInterface
{

    static public function getPath();

    static public function getTitle();

    static public function install();

}
