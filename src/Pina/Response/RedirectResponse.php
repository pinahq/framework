<?php

namespace Pina\Response;

use Pina\Request;

class RedirectResponse extends Response
{

    public function fetch(&$results, $controller, $action, $display, $isExternal)
    {
        return Request::found(!empty($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '/');
    }

}
