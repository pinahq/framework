<?php

namespace Pina\Http;

use Pina\App;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;

class Endpoint
{
    protected function getHttpRequest(): Request
    {
        return App::getActualRequest();
    }

    protected function base(): Location
    {
        return $this->getHttpRequest()->getBaseLocation();
    }

    protected function location(): Location
    {
        return $this->getHttpRequest()->getLocation();
    }

    /**
     * Request body parameters ($_POST).
     *
     * @return InputBag|ParameterBag
     */
    protected function request()
    {
        return $this->getHttpRequest()->request;
    }

    /**
     * Query string parameters ($_GET).
     *
     * @return InputBag
     */
    protected function query()
    {
        return $this->getHttpRequest()->query;
    }

    /**
     * Attribute string parameters.
     *
     * @return ParameterBag
     */
    protected function attributes()
    {
        return $this->getHttpRequest()->attributes;
    }

    /**
     * @return InputBag
     */
    protected function filters()
    {
        return $this->getHttpRequest()->filters();
    }

    /**
     * @deprecated в пользу attributes()
     * @return ParameterBag
     */
    protected function context()
    {
        return $this->attributes();
    }

    /**
     * Server and execution environment parameters ($_SERVER).
     *
     * @return ServerBag
     */
    protected function server()
    {
        return $this->getHttpRequest()->server;
    }

    /**
     * Uploaded files ($_FILES).
     *
     * @return FileBag
     */
    protected function files()
    {
        return $this->getHttpRequest()->files;
    }

    /**
     * Cookies ($_COOKIE).
     *
     * @return InputBag
     */
    protected function cookies()
    {
        return $this->getHttpRequest()->cookies;
    }

    /**
     * Headers (taken from the $_SERVER).
     *
     * @return HeaderBag
     */
    protected function headers()
    {
        return $this->getHttpRequest()->headers;
    }


}
