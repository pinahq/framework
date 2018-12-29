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

    public function fetch($type, $isConcatEnabled = false)
    {
        $links = array();
        $content = array();

        foreach ($this->data as $values) {
            if (!isset($values[$type])) {
                continue;
            }

            foreach ($values[$type] as $resource) {
                if ($isConcatEnabled && !$resource->isExternalUrl()) {
                    $content[] = $resource->getContent();
                } else {
                    $links[] = $resource->getTag();
                }
            }
        }

        if (!empty($content)) {
            $src = $this->save($type, implode("\r\n", $content));
            switch ($type) {
                case 'js': $resource = new StaticResource\Javascript(); break;
                case 'css': $resource = new StaticResource\Css(); break;
                default: throw new \Exception('');
            }
            $resource->setSrc($src);
            $links[] = $resource->getTag();
        }

        return implode("\r\n", array_unique($links));
    }

    public function save($type, $content)
    {
        $hash = md5($content);
        $path = 'cache/' . $hash . '.' . $type;
        $file = App::path() . '/../public/' . $path;
        file_put_contents($file, $content);
        return $path;
    }

    public function startLayout()
    {
        $this->mode = 'layout';
    }

}
