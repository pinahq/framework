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
    
    public function run()
    {
        if (empty($this->module)) {
            return;
        }
        $params = $this->params();

        if (!Access::isHandlerPermitted($this->resource)) {
            if (!empty($params['fallback'])) {
                $params['get'] = $params['fallback'];
                unset($params['fallback']);
                return Templater::processView($params, $this->view);
            }
            return;
        }
        
        $this->view->assign($params);
        $this->view->assign('params', $params);
        $template = $this->controller.'!'.$this->action.'!'.$this->param('display');
        
        if (!empty($params['fallback']) && !Templater::isTemplateExists($template, $this->view)) {
            $params['get'] = $params['fallback'];
            unset($params['fallback']);
            return Templater::processView($params, $this->view);
        }

        echo $this->view->fetch('pina:' .$template);
    }

}
