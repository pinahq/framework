<?php

namespace Pina;

class ResourceManager
{

    protected $data = array('layout' => array(), 'content' => array());
    protected $mode = 'content';

    public function append($type, $s)
    {
        if (empty($this->data[$this->mode][$type]) 
            || !is_array($this->data[$this->mode][$type])
        ) {
            $this->data[$this->mode][$type] = array();
        }
        $this->data[$this->mode][$type][] = $s;
    }

    public function fetch($type)
    {
        $data = array();
        foreach ($this->data as $values)
        {
            if (!isset($values[$type])) {
                continue;
            }
            $data = Arr::merge($data, $values[$type]);
        }
        return implode("\r\n", array_unique($data));
    }

    public function mode($mode = '')
    {
        if ($mode) $this->mode = $mode;
        
        return $this->mode;
    }

}
