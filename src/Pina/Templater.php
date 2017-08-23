<?php

namespace Pina;

class Templater extends \Smarty
{

    public function __construct()
    {
        parent::__construct();

        $this->strict_resources = array();
        array_unshift(
                $this->plugins_dir, __DIR__ . '/helpers'
        );

        $paths = ModuleRegistry::getPaths();
        foreach ($paths as $v) {
            $helperDir = $v . "/helpers";
            if (is_dir($helperDir)) {
                $this->plugins_dir [] = $helperDir;
            }
        }

        $this->use_sub_dirs = false;
        $this->template_dir = array();

        $template = App::template();
        if ($template && $template != 'default') {
            $this->template_dir[] = App::path() . "/" . $template . '/';
        }
        $this->template_dir[] = App::path() . "/default/";

        $this->compile_dir = App::templaterCompiled() . '/' . md5($template);
        @mkdir($this->compile_dir);

        $this->cache_dir = App::templaterCache();
        #$this->compile_check = false;

        $this->register_resource('pina', [
            "\Pina\Templater",
            "getTemplate",
            "getTemplateTimestamp",
            "getTemplateSecure",
            "getTemplateTrusted",
        ]);
        
        $this->register_resource('email', [
            "\Pina\Templater",
            "getEmailTemplate",
            "getEmailTemplateTimestamp",
            "getEmailTemplateSecure",
            "getEmailTemplateTrusted",
        ]);
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
        
        $vars_backup = $view->_tpl_vars;
        $params['get'] = Route::resource($params['get'], $params);
        $handler = new TemplaterHandler($params['get'], 'get', $params);
        $handler->setTemplater($view);
        
        $result = Request::internal($handler)->fetchContent();
        
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
        $result = Request::internal(new RequestHandler($params['get'], 'get', $params))->fetchContent();

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

    public static function getTemplatePaths($template, &$view)
    {
        list($controller, $action, $display) = explode('!', $template);
        
        $module = Route::owner($controller);
        if (empty($module)) {
            return [];
        }
        
        $handler = Url::handler($controller, $action);
        
        if (!empty($display)) {
            $handler .= '.' . $display;
        }
        
        $handler .= ".tpl";
        
        $moduleFolder = $module->getTitle();
        
        $paths = $view->template_dir;
        foreach ($paths as $k => $v) {
            $paths[$k] .= 'Modules/' . $moduleFolder . '/' . $handler;
        }
        
        $modulePath = $module->getPath();
        if (!empty($modulePath)) {
            $paths[] = $modulePath . '/' . $handler;
        }
        return array_unique($paths);
    }
    
    public static function getTemplate($template, &$tpl_source, &$view)
    {
        $paths = static::getTemplatePaths($template, $view);
        foreach ($paths as $path) {
            if (file_exists($path) && is_file($path)) {
                $tpl_source = $view->_read_file($path);
                return true;
            }
        }
        return false;
    }

    public static function getTemplateTimestamp($template, &$timestamp, &$view)
    {
        $paths = static::getTemplatePaths($template, $view);
        foreach ($paths as $path) {
            if (file_exists($path) && is_file($path)) {
                $timestamp = filemtime($path);
                return true;
            }
        }
        return false;
    }
    
    public static function isTemplateExists($template, &$view)
    {
        $paths = static::getTemplatePaths($template, $view);
        foreach ($paths as $path) {
            if (file_exists($path) && is_file($path)) {
                return true;
            }
        }
        return false;
    }

    public static function getTemplateSecure($template, &$view)
    {
        return true;
    }

    public static function getTemplateTrusted($template, &$view)
    {
        
    }
    
    public static function getEmailTemplatePaths($template, &$view)
    {
        
        $module = Request::module();
        if (empty($module)) {
            return true;
        }
        
        $handler = $template . ".tpl";
        
        $moduleFolder = substr(strrchr($module->getNamespace(), "\\"), 1);
        
        $paths = $view->template_dir;
        foreach ($paths as $k => $v) {
            $paths[$k] .= 'Modules/' . $moduleFolder . '/emails/' . $handler;
        }
        
        $modulePath = $module->getPath();
        if (!empty($modulePath)) {
            $paths[] = $modulePath . '/emails/' . $handler;
        }
        return array_unique($paths);
    }
    
    public static function getEmailTemplate($template, &$tpl_source, &$view)
    {
        $paths = static::getEmailTemplatePaths($template, $view);
        foreach ($paths as $path) {
            if (file_exists($path) && is_file($path)) {
                $tpl_source = $view->_read_file($path);
                return true;
            }
        }
        return false;
    }

    public static function getEmailTemplateTimestamp($template, &$timestamp, &$view)
    {
        $paths = static::getEmailTemplatePaths($template, $view);
        foreach ($paths as $path) {
            if (file_exists($path) && is_file($path)) {
                $timestamp = filemtime($path);
                return true;
            }
        }
        return false;
    }
    
    public static function isEmailTemplateExists($template, &$view)
    {
        $paths = static::getEmailTemplatePaths($template, $view);
        foreach ($paths as $path) {
            if (file_exists($path) && is_file($path)) {
                return true;
            }
        }
        return false;
    }

    public static function getEmailTemplateSecure($template, &$view)
    {
        return true;
    }

    public static function getEmailTemplateTrusted($template, &$view)
    {
        
    }

}
