<?php

namespace Pina\Components;

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
     * @return \Pina\Controls\RawHtml
     */
    protected function makeRawHtml()
    {
        return $this->control(\Pina\Controls\RawHtml::class);
    }

}
