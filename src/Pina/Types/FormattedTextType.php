<?php


namespace Pina\Types;

class FormattedTextType extends TextType
{

    public function draw($value): string
    {
        $value = htmlentities($value);
        $value = preg_replace('/(http[s]{0,1}\:\/\/\S{4,})(\s{0,})/ims', '<a href="$1" target="_blank">$1</a>$2', $value);
        $value = parent::draw($value);

        return $value;
    }

}