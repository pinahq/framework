<?php


namespace Pina\Controls\Record;


use Pina\App;
use Pina\Controls\Form\HandledForm;
use Pina\Controls\Form\SubmitButton;
use Pina\Data\DataTable;
use function Pina\__;

class EditableTableForm extends HandledForm
{

    /** @var EditableTableView */
    protected $table;

    /** @var SubmitButton $button */
    protected $button;

    public function __construct()
    {
        parent::__construct();
        
        $this->table = App::make(EditableTableView::class);

        $this->button = App::make(SubmitButton::class);
        $this->button->setTitle(__('Сохранить'));
    }

    public function __clone()
    {
        $this->table = clone $this->table;
        $this->button = clone $this->button;
    }

    public function setName(string $name)
    {
        $this->table->setName($name);
        return $this;
    }

    protected function drawContent(): string
    {
        return $this->table
            . ($this->table->getSchema()->isEditable() ? $this->button : '')
            . parent::drawContent();
    }

    /**
     * @param DataTable $dataTable
     */
    public function load($dataTable)
    {
        $this->table->load($dataTable);
        return $this;
    }

    /**
     * @return \Pina\Data\Schema
     */
    public function getSchema()
    {
        return $this->table->getSchema();
    }

    public function getSubmitButton()
    {
        return $this->button;
    }

}