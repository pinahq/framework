<?php

namespace Pina;

interface ResourceManagerInterface
{

    public function addCss(string $url);

    public function addCssContent(string $content);

    public function addScript(string $url);

    public function addScriptContent(string $content);

    public function fetch($type);

    public function startLayout();

}
