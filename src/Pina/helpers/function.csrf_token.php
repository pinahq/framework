<?php

use Pina\CSRF;

function smarty_function_csrf_token($params, &$view)
{
    return CSRF::token(isset($params['method'])?$params['method']:'post');
}
