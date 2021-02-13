<?php

namespace Pina;

class EventHandler extends RequestHandler
{
    private $script;

    public function __construct($module, $script, $data)
    {
        $this->module = $module;
        $this->script = $script;
        $this->data = $data;
    }

    public function data()
    {
        return $this->data;
    }

    public function run()
    {
        $path = $this->module->getPath();
        if (empty($path)) {
            return;
        }

        $path .= '/events/' . $this->script;

        if (!is_file($path . ".php")) {
            return;
        }
        include $path . ".php";
    }

}
