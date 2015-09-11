<?php

namespace Pina;

class Templater extends \Smarty
{
    
    private $layout = 'main';

    public function __construct()
    {
        parent::__construct();

        $this->strict_resources = array();
        array_unshift(
            $this->plugins_dir, __DIR__ . '/helpers'
        );
        
        $paths = Module::paths('helpers');
        foreach ($paths as $v) {
            if (is_dir($v)) {
                $this->plugins_dir [] = $v;
            }
        }

        $this->use_sub_dirs = false;
        $this->template_dir = array();
        $this->template_dir[] = App::path() . "/sites/" . Site::key() . '/';

        $template = 'default';
        if (Site::template()) {
            $template = Site::template();
            $this->template_dir[] = App::path() . "/templates/" . $template . '/';
        }
        $this->template_dir[] = App::path() . "/default/";

        $this->compile_dir = App::templaterCompiled() .'/'. md5(Site::key() . ":" . $template);
        @mkdir($this->compile_dir);

        $this->cache_dir = App::templaterCache();
    }
    
    public function setLayout($layout = "page")
    {
        if (empty($layout)) {
            return;
        }

        $this->layout = $layout;
    }
    
    public function getLayout()
    {
        return $this->layout;
    }

    public function _smarty_include($params)
    {
        $info = pathinfo($params["smarty_include_tpl_file"]);
        if ($info['extension'] != 'tpl') {
            return;
        }

        parent::_smarty_include($params);
    }

    static public function processView($params, &$view)
    {
        if (!isset($params['get'])) {
            return '';
        }
        
        $params['get'] = Route::resource($params['get'], $params);

        if (!Request::isAvailable($params['get'], 'get')) {
            return;
        }

        $vars_backup = $view->_tpl_vars;
        
        if (is_array($params)) {
            foreach ($params as $name => $value) {
                $view->assign($name, $value);
            }
        }

        list($controller, $action, $data) = Url::route($params['get'], 'get');
        $params = array_merge($params, $data);
        $handler = Url::handler($controller, $action);

        if (empty($handler)) {
            return '';
        }

        if (!empty($params['display'])) {
            $handler .= '.' . $params['display'];
        }
        
        $view->assign('params', $params);
        $result = $view->fetch('file:' . $handler . '.tpl');

        $view->_tpl_vars = $vars_backup;

        if (!empty($params['wrapper']) && !empty($result)) {
            return '<div class="' . $params['wrapper'] . '">' . $result . '</div>';
        }

        return $result;
    }

    static public function processModule($params, $view)
    {
        if (!isset($params['get'])) {
            return '';
        }
        if (!isset($params['display'])) {
            $params['display'] = '';
        }

        $vars_backup = $view->_tpl_vars;
        
        $params['get'] = Route::resource($params['get'], $params);
        $result = Request::internal($params['get'], 'get', $params);

        if (is_array($request->error_messages) && count($request->error_messages)) {
            echo '<p>' . join("<br />", $request->error_messages) . "</p>";
            return;
        }

        $view->_tpl_vars = $vars_backup;

        if (!empty($params['wrapper']) && $result) {
            return '<div class="' . $params['wrapper'] . '">' . $result . '</div>';
        }

        return $result;
    }

}
