<?php

namespace Pina\Components;

class Data extends \Pina\Controls\Control implements \Pina\ResponseInterface
{

    /**
     * @var Schema
     */
    protected $schema = null;
    protected $meta = [];
    protected $errors = [];
    protected $wrappers = [];

    public static function instance()
    {
        return new static();
    }

    public function setSchema(Schema $schema)
    {
        $this->schema = $schema;
        return $this;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function forgetField($column)
    {
        $this->schema->forgetField($column);
        return $this;
    }

    public function turnTo($alias)
    {
        return \Pina\App::components()->get($alias)->basedOn($this);
    }

    protected function control($control)
    {
        return \Pina\App::make($control);
    }

    public function setMeta($key, $value)
    {
        $this->meta[$key] = $value;
        return $this;
    }

    public function getMeta($key)
    {
        return isset($this->meta[$key]) ? $this->meta[$key] : null;
    }

    public function addError($text, $subject)
    {
        $this->errors[] = [$text, $subject];
        return $this;
    }

    public function wrap($wrapper)
    {
        return $this->pushWrapper($wrapper);
    }
    
    public function unwrap()
    {
        $this->popWrapper();
        return $this;
    }
    
    public function pushWrapper($wrapper)
    {
        array_push($this->wrappers, $wrapper);
        return $this;
    }
    
    public function popWrapper()
    {
        return array_pop($this->wrappers);
    }
    

    public function hasWrapper()
    {
        return !empty($this->wrappers);
    }

    public function compileWrappers()
    {
        $obj = $this;
        $isWrapper = false;
        $html = $obj->compile();
        foreach ($this->wrappers as $w) {
            $raw = new \Pina\Controls\RawHtml();
            $raw->setText($html);
            array_push($w->controls, $raw);
            $html = $w->draw();
            array_pop($w->controls);
        }

        return $html;
    }

    public function draw()
    {
        if (!$this->isBuildStarted()) {
            $this->startBuild();
            $this->build();
        }
        return $this->compileWrappers();
    }

    public function getType()
    {
        return 'text/html';
    }

    public function getStatus()
    {
        return $this->errors ? 400 : 200;
    }

    public function send()
    {
        header('HTTP/1.1 ' . $this->getStatus());
        if ($this->errors) {
            header('Content-Type: application/json');
            return json_encode(['errors' => $this->errors], JSON_UNESCAPED_UNICODE);
        }
        header('Content-Type: ' . $this->getType());
        echo $this->draw();
    }

}
