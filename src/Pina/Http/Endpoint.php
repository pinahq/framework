<?php

namespace Pina\Http;

use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;

class Endpoint
{

    /**
     *
     * @var Request
     */
    protected $request;

    /**
     *
     * @var Location
     */
    protected $location;

    /**
     *
     * @var Location
     */
    protected $base;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->location = $request->getLocation();
        $this->base = $request->getBaseLocation();
    }

    /**
     * Request body parameters ($_POST).
     *
     * @return InputBag|ParameterBag
     */
    public function request()
    {
        return $this->request->request;
    }

    /**
     * Query string parameters ($_GET).
     *
     * @return InputBag
     */
    public function query()
    {
        return $this->request->query;
    }

    /**
     * Attribute string parameters.
     *
     * @return ParameterBag
     */
    public function attributes()
    {
        return $this->request->attributes;
    }

    /**
     * @return InputBag
     */
    public function filters()
    {
        return $this->request->filters();
    }

    /**
     * @return InputBag
     */
    public function context()
    {
        return $this->request->context();
    }

    /**
     * Server and execution environment parameters ($_SERVER).
     *
     * @return ServerBag
     */
    public function server()
    {
        return $this->request->server;
    }

    /**
     * Uploaded files ($_FILES).
     *
     * @return FileBag
     */
    public function files()
    {
        return $this->request->files;
    }

    /**
     * Cookies ($_COOKIE).
     *
     * @return InputBag
     */
    public function cookies()
    {
        return $this->request->cookies;
    }

    /**
     * Headers (taken from the $_SERVER).
     *
     * @return HeaderBag
     */
    public function headers()
    {
        return $this->request->headers;
    }


}
