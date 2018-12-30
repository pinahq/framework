<?php

namespace Pina;

class ResourceManager
{

    protected $data = array('layout' => array(), 'content' => array());
    protected $mode = 'content';

    public function append(\Pina\StaticResource\StaticResource $resource)
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
