<?php

namespace Pina\Components;

/**
 * Выпадающий список
 */
class SelectComponent extends ListData
{

    protected $title = '';
    protected $name = '';
    protected $value = '';
    protected $placeholder = null;

    /**
     * Настроить название выпадаюшего списка
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Настроить HTML-аттрибут name
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Указать активный элемент выпадающего списка
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function build()
    {
        $select = $this->makeFormSelect();
        $select->setVariants($this->getData());
        $select->setName($this->name);
        $select->setTitle($this->title);
        $select->setValue($this->value);
        $select->setPlaceholder($this->placeholder);

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
