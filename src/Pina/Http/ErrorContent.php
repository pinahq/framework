<?php

namespace Pina\Http;

use Pina\App;
use Pina\ContentInterface;
use Pina\Controls\ErrorPage;

class ErrorContent implements ContentInterface
{
    protected $code;
    protected $errors = [];

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function fetch()
    {
        $mime = App::negotiateMimeType();
        switch ($mime) {
            case 'application/json':
            case 'text/json':
                return json_encode(['code' => $this->code, 'errors' => $this->errors], JSON_UNESCAPED_UNICODE);
        }

        /** @var ErrorPage $view */
        $view = App::make(ErrorPage::class);
        $view->load($this->code);

        return $view;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function getType()
    {
        $mime = App::negotiateMimeType();
        switch ($mime) {
            case 'application/json':
            case 'text/json':
                return 'application/json; charset=' . App::charset();
        }
        return 'text/html; charset=' . App::charset();
    }

}