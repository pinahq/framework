<?php

namespace Pina;

use Pina\StaticResource\ModuleScript;
use Pina\StaticResource\Script;
use Pina\StaticResource\StaticResource;
use Pina\StaticResource\Style;

class ResourceManager implements ResourceManagerInterface
{

    protected $data = array('layout' => array(), 'content' => array());
    protected $mode = 'content';

    public function addStyle(string $url)
    {
        $this->append((new Style())->setSrc($url));
    }

    public function addStyleContent(string $content)
    {
        $this->append((new Style())->setContent($content));
    }

    public function addScript(string $url)
    {
        $this->append((new Script())->setSrc($url));
    }

    public function addModuleScript(string $url)
    {
        $this->append((new ModuleScript())->setSrc($url));
    }

    public function addScriptContent(string $content)
    {
        $this->append((new Script())->setContent($content));
    }

    /**
     * @param StaticResource $resource
     */
    protected function append(StaticResource $resource)
    {
        $type = $resource->getType();
        if (empty($this->data[$this->mode][$type]) || !is_array($this->data[$this->mode][$type])
        ) {
            $this->data[$this->mode][$type] = array();
        }
        $this->data[$this->mode][$type][] = $resource;
    }

    public function fetch($type)
    {
        $items = array();
        foreach ($this->data as $values) {
            if (!isset($values[$type])) {
                continue;
            }

            foreach ($values[$type] as $resource) {
                $items[] = $resource->getTag();
            }
        }

        return implode("\r\n", array_unique($items));
    }

    public function startLayout()
    {
        $this->mode = 'layout';
    }

}
