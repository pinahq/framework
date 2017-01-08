<?php

namespace Pina\Response;

class JsonResponse extends Response
{

    public function contentType()
    {
        return 'application/json';
    }

    public function fetch($results, $controller, $action, $display, $isExternal)
    {
        return json_encode($results, JSON_UNESCAPED_UNICODE);
    }

}
