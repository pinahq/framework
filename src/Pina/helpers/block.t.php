<?php

function smarty_block_t($ps, $content, &$view, &$repeat) {
    if ($repeat) {
        return;
    }
    
    return \Pina\Language::translate($content);
}