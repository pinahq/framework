<?php

namespace Pina\Components;

use Pina\Controls\RawHtml;

/**
 * Текст, выбранный в селекторе
 */
class SelectedTextComponent extends SelectComponent
{

    public function build()
    {
        $control = $this->makeRawHtml();

        $data = $this->getData();
        foreach ($data as $item) {
            if ($item['id'] == $this->value) {
                $control->setText($item['title']);
            }
        }

        $this->append($control);
    }

    /**
     * @return RawHtml
     */
    protected function makeRawHtml()
    {
        return $this->control(RawHtml::class);
    }

}
