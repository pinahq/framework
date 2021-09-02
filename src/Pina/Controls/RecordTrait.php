<?php

namespace Pina\Controls;

use Pina\App;
use Pina\Data\DataRecord;

trait RecordTrait
{
    /**
     * @var DataRecord
     */
    protected $record;

    /**
     * @param DataRecord $record
     */
    public function load($record)
    {
        $this->record = $record;
        return $this;
    }

    public function getPayload()
    {
        return $this->record;
    }

}