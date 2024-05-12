<?php


namespace Pina\Controls;

use Pina\App;
use Pina\Html;

class EditableRecordRow extends RecordRow
{
    protected $name = '';

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function drawInner()
    {
        $content = '';
        $data = $this->record->getData();
        $html = $this->record->getHtmlData();
        foreach ($this->record->getSchema()->getIterator() as $field) {
            if ($field->isHidden()) {
                continue;
            }

            $name = $field->getName();
            if ($field->isStatic()) {
                $content .= Html::tag('td', $html[$name] ?? '');
            } else {
                $type = $field->getType();
                $value = isset($data[$name]) ? $data[$name] : null;
                $pk = $this->record->getSchema()->getPrimaryKey();

                $nameBrackets = '';
                foreach ($pk as $pkElement)  {
                    $idElement = !empty($data[$pkElement]) ? $data[$pkElement] : '';
                    $nameBrackets .= '[' . $idElement . ']';
                }

                $cell = App::type($type)->setContext($data)->makeControl($field, $value);
                $cell->setName($this->name . $nameBrackets . '[' . $name . ']');
                $cell->setCompact();
                $content .= Html::tag('td', $cell);
            }
        }
        return $content;
    }
}