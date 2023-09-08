<?php

namespace Pina;

interface ResourceManagerInterface
{

    public function addStyle(string $url);

    public function addStyleContent(string $content);

    public function addScript(string $url);

    public function addScriptContent(string $content);

    public function fetch($type);

    public function startLayout();

}
