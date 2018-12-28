<?php

namespace Pina;

interface ResourceManagerInterface
{

    public function append($type, $s);

    public function fetch($type);

    public function mode($mode = '');

}
