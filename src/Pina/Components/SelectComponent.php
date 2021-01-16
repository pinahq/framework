<?php

namespace Pina\Components;

class SelectComponent extends ListData
{

    protected $title = '';
    protected $name = '';
    protected $value = '';

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function build()
    {
        $select = $this->makeFormSelect();
        $select->setVariants($this->getData());
        $select->setName($this->name);
        $select->setTitle($this->title);
        $select->setValue($this->value);

        $this->append($select);
    }

    /**
     * @return \Pina\Controls\FormSelect
     */
    protected function makeFormSelect()
    {
        return $this->control(\Pina\Controls\FormSelect::class);
    }

}
