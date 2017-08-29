<?php

namespace Pina;

interface ContentInterface
{

    public function fetch();
    public function setErrors($errors);
    public function getType();

}
