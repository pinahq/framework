<?php

namespace Pina;

class EmptyContent implements ContentInterface
{

    public function setErrors($errors)
    {
    }
    
    public function getType()
    {
        return 'text/html; charset=' . App::charset();
    }

    public function fetch()
    {
        return '';
    }

}
