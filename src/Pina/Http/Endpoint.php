<?php

namespace Pina\Http;

use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;

class Endpoint
{

    /**
     *
     * @var Request
     */
    protected $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
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
