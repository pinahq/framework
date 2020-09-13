<?php

namespace Pina\Components;

use Pina\Controls\FormStatic;

class RecordViewComponent extends RecordData //implements ComponentInterface
{

    public function build()
    {
        $fields = $this->schema->getFields();
        $titles = $this->schema->getTitles();

        foreach ($fields as $k => $field) {
            $title = $titles[$k] ? $titles[$k] : '';
            $value = $this->data[$field] ? $this->data[$field] : '';
            $static = $this->makeFormStatic()->setTitle($title)->setValue($value);
            $this->append($static);
        }
        
    }

    /**
     * @return \Pina\Controls\FormStatic
     */
    protected function makeFormStatic()
    {
        return $this->control(\Pina\Controls\FormStatic::class);
    }

}
