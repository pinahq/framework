<?php

function smarty_block_t($ps, $content, &$view, &$repeat) {
    if ($repeat) {
        return;
    }
    
    $translated = \Pina\Language::translate($content);
    if (empty($ps)) {
        return $translated;
    }
    
    $translated = str_replace('%', '%s', $translated);
    ksort($ps);
    return vsprintf($translated, $ps);
}