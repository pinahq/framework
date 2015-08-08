<?php

namespace Pina\Response;

use Pina\App;
use Pina\Language;
use Pina\Templater;
use Pina\ResourceManager;

class HtmlResponse extends Response
{

    public $view;

    public function __construct($view = null)
    {
        if (empty($view)) {
            $view = new Templater();
        }

        $this->view = $view;
    }

    public function fail()
    {
        $message = '';
        foreach ($this->messages as $k => $v) {
            $message .= $v[1]."\r\n";
        }
        
        echo '<html><head><meta charset="'.App::charset().'" /></head><body>'.$message.'</body></html>';
        $this->badRequest();
        exit;
    }

    public function result($name, $value)
    {
        // привязываем к view соответствующие переменные
        $this->view->assign($name, $value);
    }

    public function fetch($handler = '', $first = true)
    {
        $this->header('Pina-Response: Json');
        $this->contentType('text/html');

        if ($first && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
        ) {
            $first = false;
        }
        
        $app = App::get();

        $this->view->assign('params', \Pina\Request::params());
        $t = $this->view->fetch('Modules/' . $handler . '.tpl');
        if ($first) {
            $this->view->assign("content", $t);
            ResourceManager::mode('layout');
            $t = $this->view->fetch('Layout/'.$app.'/'. $this->view->getLayout(). '.tpl');
        }

        //\Pina\Modules\Core\Language::rewrite($t);
        return $t;
    }

}
