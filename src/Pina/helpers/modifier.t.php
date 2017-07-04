<?php

function smarty_modifier_t($string)
{
    return \Pina\Language::translate($string);
}