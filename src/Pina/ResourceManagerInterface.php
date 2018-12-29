<?php

namespace Pina;

interface ResourceManagerInterface
{

    public function append(\Pina\StaticResource\StaticResource $resource);

    public function fetch($type);

    public function startLayout();

}
