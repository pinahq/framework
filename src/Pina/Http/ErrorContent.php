<?php

namespace Pina\Http;

use Pina\App;
use Pina\ContentInterface;
use Pina\Controls\Control;
use Pina\Controls\ErrorPage;
use Pina\JsonContent;
use Pina\Layouts\DefaultLayout;

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

        $layout = $view->getLayout();
        if ($layout) {
            return $view->wrap($layout);
        }
        return $view->wrap(App::make(DefaultLayout::class));
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