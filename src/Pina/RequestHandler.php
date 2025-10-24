<?php

namespace Pina;

class RequestHandler
{

    protected $data = [];
    protected $module = '';
    protected $layout = '';
    protected $places = [];
    protected $resource = null;
    private $raw = '';
    private $method = null;
    private $controller = '';
    private $action = '';

    public function __construct($resource, $method, $data)
    {
        if (is_array($data)) {
            $this->data = $data;
        } else {
            $this->raw = $data;
        }

        $this->resource = $resource;
        $this->method = $method;

        list($this->controller, $this->action, $parsed) = Url::route($resource, $method);
        foreach ($parsed as $k => $v) {
            if (is_null($v) && $this->exists($k)) {
                continue;
            }
            $this->set($k, $v);
        }

        $this->module = Route::owner($this->controller);
        $this->layout = 'main';
    }

    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function setPlace($place, $content)
    {
        $this->places[$place] = $content;
    }

    public function getPlace($place)
    {
        if (!isset($this->places[$place])) {
            return '';
        }
        return $this->places[$place];
    }

    public function mergePlaces(RequestHandler $handler)
    {
        $this->places = array_merge($handler->places, $this->places);
    }

    public function isolation()
    {
        return false;
    }

    public function resource()
    {
        return $this->resource;
    }

    public function controller()
    {
        return $this->controller;
    }

    public function raw()
    {
        if (!empty($this->raw)) {
            return $this->raw;
        }

        return file_get_contents('php://input');
    }

    public function exists($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $input = $this->all();

        foreach ($keys as $value) {
            if (!Arr::has($input, $value)) {
                return false;
            }
        }

        return true;
    }

    public function has($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        foreach ($keys as $k) {

            $value = $this->input($k) ?? "";

            $boolOrArray = is_bool($value) || is_array($value);
            if (!$boolOrArray && trim($value) === '') {
                return false;
            }
        }

        return true;
    }

    public function all()
    {
        return $this->data;
    }

    public function input($name, $default = null)
    {
        return Arr::get($this->data, $name, $default);
    }

    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $r = [];

        $input = $this->all();

        foreach ($keys as $key) {
            Arr::set($r, $key, Arr::get($input, $key));
        }

        return $r;
    }

    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $r = $this->all();

        Arr::forget($r, $keys);

        return $r;
    }

    public function intersect($keys)
    {
        return array_filter($this->only(is_array($keys) ? $keys : func_get_args()));
    }

    public function method()
    {
        return $this->method;
    }

    public function module()
    {
        return $this->module;
    }

    public function run()
    {
        if (!Access::isHandlerPermitted($this->resource)) {
            return $this->forbidden();
        }

        if (App::router()->exists($this->resource, $this->method)) {
            $data = App::router()->run($this->resource, $this->method, $this->data);
            if ($data instanceof Response) {
                if (!$data->hasContent()) {
                    $content = App::createResponseContent([], $this->controller, $this->action);
                    $data->setContent($content);
                }
                return $data;
            }

            $content = new TemplateLayoutContent;
            if (Request::isExternalRequest()) {
                //$content->drawLayout($data->drawWithWrappers());
                $layout = $data->getLayout();
                $r = $layout->append($data)->drawWithWrappers();
                $content->setContent($r);
            } else {
                $content->setContent($data->drawWithWrappers());
            }
            return Response::ok()->setContent($content);
        }

        if (empty($this->module)) {
            return $this->notFound();
        }

        $handler = $this->module->getPath() . '/' . Url::handler($this->controller, $this->action);
        if (!is_file($handler . ".php")) {
            return $this->notFound();
        }
        $r = include $handler . ".php";

        if (empty($r)) {
            return Response::ok();
        }

        if ($r instanceof ResponseInterface) {
            if (!$r->hasContent()) {
                $content = App::createResponseContent([], $this->controller, $this->action);
                $r->setContent($content);
            }
            return $r;
        }

        $content = App::createResponseContent($r, $this->controller, $this->action);
        return Response::ok()->setContent($content);
    }

    private function forbidden()
    {
        if (!empty($this->data['fallback']) && $this->data['fallback'] != $this->resource) {
            return $this->fallback();
        }
        unset($this->data['display']);
        return Response::forbidden();
    }

    private function notFound()
    {
        if (!empty($this->data['fallback']) && $this->data['fallback'] != $this->resource) {
            return $this->fallback();
        }
        unset($this->data['display']);
        return Response::notFound();
    }

    private function fallback()
    {
        $data = $this->data;
        unset($data['fallback']);
        $data['get'] = Route::resource($this->data['fallback'], $data);
        return Request::internal(new RequestHandler($data['get'], $this->method, $data));
    }

}
