<?php

function smarty_function_view($params, &$view) {
    return \Pina\Legacy\Templater::processView($params, $view);
}
