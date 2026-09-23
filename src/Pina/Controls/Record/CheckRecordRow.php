<?php

namespace Pina\Controls\Record;

use Pina\App;
use Pina\Controls\Components\Checkbox;
use Pina\Controls\Wrapper;

class CheckRecordRow extends RecordRow
{
    protected function drawContent(): string
    {
        return $this->makeCheck() . parent::drawContent();
    }

    public function makeCheck()
    {
        $pk = $this->record->getPrimaryKey();
        $keys = [];
        foreach ($pk as $part) {
            $keys[] = $this->record->getValue($part);
        }

        /** @var Checkbox $checkbox */
        $checkbox = App::make(Checkbox::class);
        $checkbox->setId('bulk_edit_key_' . implode('_', $keys));
        $checkbox->addClass('bulk-edit-checkbox');
        $keyPath = [];
        foreach ($keys as $item) {
            $keyPath[] = '[' . $item . ']';
        }
        $checkbox->setName('bulk_edit_key' . implode('', $keyPath));
        $checkbox->setValue(1);
        $checkbox->wrap(new Wrapper("td"));
        return $checkbox;
    }
}