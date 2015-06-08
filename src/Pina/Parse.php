<?php

namespace Pina;

class Parse
{

    static public function betweenMarkers(&$content, $markerBegin, $markerEnd, $left = 0)
    {
        $pos = strpos($content, $markerBegin, $left);

        if ($pos === false) {
            return false;
        }

        $posStart = $pos + strlen($markerBegin);
        $posEnd = strpos($content, $markerEnd, $posStart);

        if ($posEnd === false) {
            return false;
        }

        $data = substr($content, $posStart, $posEnd - $posStart);

        return $data;
    }

}