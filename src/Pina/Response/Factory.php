<?php

namespace Pina\Response;

use Pina\App;

class Factory {
    static public function get($resource, $method)
    {
        $response = null;
        
        $info = pathinfo($resource);
        $extension = !empty($info['extension']) ? $info['extension'] : '';
        if (in_array($extension, array('', 'html')) && $method == "get") {
            $response = new HtmlResponse();
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                $response->setLayout('block');
            }
        } elseif ($extension == 'json' ||
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
            $response = new JsonResponse();
        } else {
            $response = new RedirectResponse();
        }
        
        return $response;
    }
}