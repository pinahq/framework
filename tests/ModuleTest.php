<?php

use PHPUnit\Framework\TestCase;
use Pina\Arr;

class ModuleTest extends TestCase
{
    
    public function testPaths()
    {
        $modules = new \Pina\ModuleRegistry();
        $modules->load(\Pina\Module::class);
        $paths = $modules->getPaths();
        $l = strlen(__DIR__) - strlen('/tests');
        $base = substr(__DIR__, 0, $l);
        $path = $base . '/src/Pina';
        $this->assertTrue(in_array($path, $paths));
    }
    
}