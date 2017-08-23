<?php

namespace Pina;

class TemplaterHandler extends RequestHandler
{

    private $resource = null;
    private $method = null;
    private $controller = '';
    private $action = '';
    private $view = null;

    public function __construct($resource, $method, $data)
    {
        if (is_array($data)) {
            $this->data = $data;
        } else {
            $this->raw = $data;
        }

        $this->resource = $resource;
        $this->method = $method;

        list($this->controller, $this->action, $parsed) = Url::route($resource, $method);
        foreach ($parsed as $k => $v) {
            $this->set($k, $v);
        }

        $this->module = Route::owner($this->controller);
        $this->layout = 'main';
    }

    public function setTemplater($view)
    {
        $this->view = $view;
    }

    public function fallback($params)
    {
        $params['get'] = $params['fallback'];
        unset($params['fallback']);
        
        $params['get'] = Route::resource($params['get'], $params);
        list($controller, $action, $parsed) = Url::route($params['get'], 'get');
        $params = array_merge($params, $parsed);
        
        $template = 'pina:' . $controller . '!' . $action . '!' . Request::input('display');
        $content = new TemplaterContent($params, $template, Request::isExternalRequest());
        
        return Response::ok()->setContent($content);
    }

    public function run()
    {
        if (empty($this->module)) {
            return Response::notFound();
        }
        $params = $this->all();

        if (!Access::isHandlerPermitted($this->resource)) {
            if (!empty($params['fallback'])) {
                return $this->fallback($params);
            }
            return Response::forbidden();
        }

        if (!empty($params['fallback']) && !Templater::isTemplateExists($template, $this->view)) {
            return $this->fallback($params);
        }
        $template = 'pina:' . $this->controller . '!' . $this->action . '!' . Request::input('display');
        $content = new TemplaterContent($params, $template, Request::isExternalRequest());
        return Response::ok()->setContent($content);
    }

}
