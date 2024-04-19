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
        if (!empty($message)) {
            $this->addError($message, $code);
        }
    }

    public function setErrors($errors)
    {
        foreach ($errors as $error) {
            $message = $error[0] ?? '';
            $field = $error[1] ?? '';
            $this->addError($message, $field);
        }
    }

    public function addError($message, $field)
    {
        $this->errors[] = [$message, $field];
        $this->message .= $message . ($field ? ' (' . $field .')' : ''). '; ';
    }

    public function getErrors()
    {
        return $this->errors;
    }

}