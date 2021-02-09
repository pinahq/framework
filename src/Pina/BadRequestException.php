<?php

namespace Pina;

use RuntimeException;
use Throwable;

class BadRequestException extends RuntimeException
{

    protected $errors = [];

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->addError($message, $code);
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function addError($message, $field)
    {
        $this->errors[] = [$message, $field];
    }

    public function getErrors()
    {
        return $this->errors;
    }

}
