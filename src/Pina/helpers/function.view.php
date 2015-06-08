<?php

function smarty_function_view($params, &$view) {
    return \Pina\Templater::processView($params, $view);
}
