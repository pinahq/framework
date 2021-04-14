<?php

namespace Pina;

interface InstallationInterface
{
    
    /**
     * Выполняется перед патчем БД
     */
    public function prepare();

    /**
     * Выполняется после патча БД
     */
    public function install();

    /**
     * Выполняется при удалении модуля
     */
    public function remove();

}
