<?php

namespace Pina;

class Html
{

    public static function tag($tag, $content)
    {
        return '<' . $tag . '>' . $content . '</' . $tag . '>';
    }

}
