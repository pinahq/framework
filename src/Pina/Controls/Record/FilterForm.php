<?php

namespace Pina\Controls\Record;

use Pina\App;
use Pina\Controls\Form\SubmitButton;
use function Pina\__;

/**
 * Форма с фильтрами
 */
class FilterForm extends RecordForm
{

    public function __construct()
    {
        parent::__construct();
        $this->classes = [];
        $this->addClass($this->formClass);
        $this->addClass('form');
    }

    protected function draw(): string
    {
        if ($this->record->getSchema()->isEmpty()) {
            return '';
        }

        return parent::draw();
    }

    /**
     * @return SubmitButton
     */
    protected function makeSubmit()
    {
        return App::make(SubmitButton::class)->setTitle(__('Искать'));
    }

}