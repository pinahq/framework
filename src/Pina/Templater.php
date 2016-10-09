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

        $paths = ModuleRegistry::getPaths();
        foreach ($paths as $v) {
            $helperDir = $v."/helpers";
            if (is_dir($helperDir)) {
                $this->plugins_dir [] = $helperDir;
            }
        }

        $this->use_sub_dirs = false;
        $this->template_dir = array();

        $template = 'default';
        if (App::template()) {
            $template = App::template();
            $this->template_dir[] = App::path() . "/templates/" . $template . '/';
        }
        $this->template_dir[] = App::path() . "/default/";

        $this->compile_dir = App::templaterCompiled() . '/' . md5($template);
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
        
        list($controller, $action, $data) = Url::route($params['get'], 'get');
        
        $module = Route::owner($controller);

        if (!Request::isAvailable($module, $params['get'])) {
            return;
        }

        $vars_backup = $view->_tpl_vars;

        if (is_array($params)) {
            foreach ($params as $name => $value) {
                $view->assign($name, $value);
            }
        }

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
            list($start, $end) = static::wrapper($params['wrapper']);
            return $start . $result . $end;
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

        if (!empty($params['wrapper']) && !empty($result)) {
            list($start, $end) = static::wrapper($params['wrapper']);
            return $start . $result . $end;
        }

        return $result;
    }

    static private function wrapper($rest)
    {
        if (empty($rest)) {
            return array('', '');
        }
        $params = array();
        while (1) {
            $pos = strrpos($rest, '=');
            if ($pos === false) {
                break;
            }
            $value = trim(substr($rest, $pos + 1), ' \'"');
            $rest = trim(substr($rest, 0, $pos));
            $pos = strrpos($rest, ' ');
            if ($pos === false) {
                break;
            }
            $key = trim(substr($rest, $pos + 1));
            $rest = trim(substr($rest, 0, $pos));

            array_push($params, ' ' . $key . '="' . $value . '"');

            $i++;
            if ($i > 10) {
                break;
            }
        }
        $tag = trim($rest);

        $start = '<' . $tag;
        while ($p = array_pop($params)) {
            $start .= $p;
        }
        $start .= '>';
        $end = '</' . $tag . '>';
        return array($start, $end);
    }

}
