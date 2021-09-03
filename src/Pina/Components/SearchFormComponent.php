<?php

namespace Pina\Components;

use function Pina\__;

/**
 * @deprecated см \Pina\Controls\FilterForm
 */
class SearchFormComponent extends RecordFormComponent
{

    public function build()
    {
        if ($this->schema->isEmpty()) {
            return;
        }

        $this->buttonRow->setMain($this->makeSubmit()->setTitle(__('Искать')));

        $this->append($this->buildForm());
    }

}
