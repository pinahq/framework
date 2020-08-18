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
        return Registry::get($alias)->basedOn($this);
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
    }
    
    public function wrap($wrapper)
    {
        $this->wrappers[] = $wrapper;
    }
    
    public function hasWrapper()
    {
        return !empty($this->wrappers);
    }
    
    public function compileWrappers()
    {
        $obj = $this;
        $isWrapper = false;
        while ($w = array_shift($this->wrappers)) {
            $w->append($obj);
            $obj = $w;
            $isWrapper = true;
        }

        return $isWrapper ? $obj->draw() : $obj->compile();
    }

    public function draw()
    {
        $this->build();
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
