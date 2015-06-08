<?php

use Pina\App;
use Pina\Composer;

function smarty_function_form($ps, &$view)
{
    $r = '<form';
    
    $prefix = '';
    $app = !empty($ps['app'])?$ps['app']:App::get();
    $apps = App::apps();
    if (!empty($apps[$app])) $prefix = $apps[$app]."/";
    
    $ps['action'] = $prefix.$ps['action'];
    

    $add = '';
    if (!empty($ps["action"]) && strpos($ps['action'], '/:') !== false) {
        foreach ($ps as $k => $p) {
            $ps["action"] = str_replace(':' . $k, $p, $ps["action"]);
        }
    }
    
    $resource = $ps['action'];
    
    if (!empty($ps['action']) && !empty($ps['method'])) {
        $ps['method'] = strtolower($ps['method']);
        
        $add .= '<input type="hidden" name="'.$ps['method'].'" value="'.$ps['action'].'" />';
        $ps['action'] = '/pina.php';
        if ($ps['method'] != 'get') {
            $ps['method'] = 'post';
        }
    }
    
    if (!empty($ps["action"])) {
        $r .= ' action="' . $ps["action"] . '"';
    }
    if (!empty($ps["method"])) {
        $r .= ' method="' . $ps["method"] . '"';
    }
    if (!empty($ps["id"])) {
        $r .= ' id="' . $ps["id"] . '"';
    }
    if (!empty($ps["name"])) {
        $r .= ' name="' . $ps["name"] . '"';
    }
    if (!empty($ps["class"])) {
        $r .= ' class="' . $ps["class"] . '"';
    }
    if (!empty($ps["enctype"])) {
        $r .= ' enctype="' . $ps["enctype"] . '"';
    }
    if (!empty($ps["novalidate"])) {
        $r .= ' novalidate="' . $ps["novalidate"] . '"';
    }
    if (!empty($ps["role"])) {
        $r .= ' role="' . $ps["role"] . '"';
    }
    if (!empty($ps["target"])) {
        $r .= ' target="' . $ps["target"] . '"';
    }
    $r .= '>';
    $r .= $add;
    
    $ps['resource'] = $resource;
    $r .= Composer::draw(
        'templater::form', 
        $ps,
        $view
    );
    //$r .= CSRFToken::formField($resource, $ps["method"]);

    return $r;
}