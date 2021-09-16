<?php

namespace Pina\Components;

use Pina\App;
use Pina\Controls\Control;
use Pina\ResponseInterface;
use Pina\Data\Schema;

abstract class Data extends Control implements ResponseInterface
{

    /**
     * @var Schema
     */
    protected $schema = null;
    protected $meta = [];
    protected $errors = [];

    abstract protected function build();

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

    /**
     * @param $alias
     * @return Data
     */
    public function turnTo($alias)
    {
        return App::components()->get($alias)->basedOn($this);
    }

    protected function control($control)
    {
        return App::make($control);
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

    protected function draw()
    {
        if (!$this->isBuildStarted()) {
            $this->startBuild();
            $this->build();
        }
        return $this->compile();
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
            echo json_encode(['errors' => $this->errors], JSON_UNESCAPED_UNICODE);
            return;
        }
        header('Content-Type: ' . $this->getType());
        echo $this->draw();
    }


    /////////////////ВРЕМЕННО ЗАБИРАЕТ К СЕБЕ ЛОГИКУ СБОРКИ ИЗ КОНТРОЛА, КОТОРАЯ УЖЕ НЕ АКТУАЛЬНА

    protected $isBuildStarted = false;

    /**
     * @var Control[]
     */
    protected $controls = [];

    /**
     * @var Control[]
     */
    protected $innerAfter = [];

    /**
     * @var Control[]
     */
    protected $innerBefore = [];


    /**
     * @return void
     */
    public function startBuild()
    {
        $this->isBuildStarted = true;
    }

    /**
     * @return bool
     */
    public function isBuildStarted()
    {
        return $this->isBuildStarted;
    }

    /**
     * @param Control $control
     * @return $this
     */
    public function append($control)
    {
        if ($this->isBuildStarted) {
            //пошла сборка контролов по запросу отрисовки
            $this->controls[] = $control;
        } else {
            //запоминаем, какие контролы хотят отрисоваться после основного блока
            $this->innerAfter[] = $control;
        }
        return $this;
    }

    /**
     * @param Control $control
     * @return $this
     */
    public function prepend($control)
    {
        array_unshift($this->innerBefore, $control);
        return $this;
    }

    /**
     * @return string
     */
    protected function compile()
    {
        $r = '';
        foreach ($this->innerBefore as $c) {
            $r .= $c->drawWithWrappers();
        }
        foreach ($this->controls as $c) {
            $r .= $c->drawWithWrappers();
        }
        foreach ($this->innerAfter as $c) {
            $r .= $c->drawWithWrappers();
        }
        return $r;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->innerBefore) + count($this->controls) + count($this->innerAfter);
    }
}
