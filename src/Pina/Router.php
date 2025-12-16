<?php

namespace Pina;

use Exception;
use Pina\Http\Request;
use Pina\Model\LinkedItem;
use Pina\Model\LinkedItemCollection;
use Pina\Router\DispatcherInterface;
use Pina\Router\RouteGroup;

class Router
{
    /** @var \Pina\Router\Route[]  */
    protected $items = [];

    /** @var DispatcherInterface[] */
    protected $dispatchers = [];

    protected $fallbacks = [];

    /**
     * @param string $pattern
     * @param string $class
     */
    public function register($pattern, $class, $context = []): \Pina\Router\Route
    {
        $route = new \Pina\Router\Route($pattern, $class, $context);
        $this->items[$route->getController()] = $route;
        return $route;
    }

    public function registerDispatcher($dispatcher)
    {
        $this->dispatchers[] = $dispatcher;
    }

    public function isPermitted($resource)
    {
        return App::access()->isHandlerPermitted($resource);
    }

    public function makeGroup($tags = [])
    {
        return new RouteGroup($this, $tags);
    }

    public function getPatterns()
    {
        $patterns = [];
        foreach ($this->items as $item) {
            $patterns[] = $item->getPattern();
        }
        return $patterns;
    }

    /**
     * @param string $resource
     * @param string $method
     * @return bool
     */
    public function exists($resource, $method)
    {
        list($controller, $action, $params) = Url::route($resource, $method);

        $c = $this->base($controller);

        if (is_null($c)) {
            return false;
        }

        $action .= $this->calcDeeperAction($resource, $this->items[$c]->getPattern());

        return method_exists($this->items[$c]->getEndpoint(), $action);
    }

    public function getEndpointClass($resource): ?string
    {
        $controller = Url::controller($resource);

        $c = $this->base($controller);

        if (is_null($c)) {
            return null;
        }

        return isset($this->items[$c]) ? $this->items[$c]->getEndpoint() : null;
    }

    /**
     *
     * @param string $resource
     * @param string $method
     * @param array $data
     * @return mixed
     * @throws Container\NotFoundException
     */
    public function run($resource, $method, $data = [])
    {
        $resource = $this->dispatch($resource);

        list($controller, $action, $params) = Url::route($resource, $method);
        if (!CSRF::verify($controller, $data)) {
            return Response::forbidden();
        }

        if (!$this->isPermitted($resource)) {
            return Response::forbidden();
        }

        $c = $this->base($controller);
        if ($c === null) {
            return $this->fallback($resource, $method, $data);
        }

        $pattern = $this->items[$c]->getPattern();

        App::pushRequest($this->makeRequest($resource, $c, $data, $pattern));
        try {
            $inst = $this->items[$c]->makeEndpoint();

            $action .= $this->calcDeeperAction($resource, $pattern);

            if (!method_exists($inst, $action)) {
                throw new NotFoundException();
            }

            $r = call_user_func_array([$inst, $action], array_values($params));
        } catch (\Exception $e) {
            throw $e;
        } finally {
            App::popRequest();
        }

        return $r;
    }

    protected function makeRequest($resource, $c, $data, $pattern)
    {
        $parsed = [];
        $this->parse($resource, $pattern, $parsed);

        $request = new Request($_GET, Input::getData(), array_merge($data, $parsed), $_COOKIE, $_FILES, $_SERVER);

        $location = App::baseUrl()->location($resource);

        $controllerCount = count(explode('/', $c));
        $amount = $controllerCount * 2 - 1;

        $baseResource = implode('/', array_slice(explode('/', trim($resource, '/')), 0, $amount));

        $base = App::baseUrl()->location($baseResource);

        $request->setLocation($base, $location);

        $request->setContext($this->items[$c]->getContext());

        return $request;
    }

    protected function dispatch(string $resource): string
    {
        foreach ($this->dispatchers as $dispatcher) {
            if ($r = $dispatcher->dispatch($resource)) {
                return $r;
            }
        }
        return $resource;
    }

    protected function fallback($resource, $method, $data)
    {
        foreach ($this->fallbacks as $fallback) {
            $router = App::load($fallback);
            if ($router->exists($resource, $method)) {
                return $router->run($resource, $method, $data);
            }
        }
        throw new Container\NotFoundException;
    }

    public function addFallback($class)
    {
        $this->fallbacks[] = $class;
    }

    public function findChilds(string $resource)
    {
        list($controller, $action, $params) = Url::route($resource, 'get');
        $prefix = $controller . '/';
        $found = [];
        foreach ($this->items as $c => $e) {
            if (strpos($c, $prefix) !== 0) {
                continue;
            }
            $right = substr($c, strlen($prefix));
            if (strpos($right, '/') !== false) {
                continue;
            }
            $found[] = $resource . '/' . $right;
        }
        return $found;
    }

    /**
     * @return LinkedItemCollection
     */
    public function getMenu(): LinkedItemCollection
    {
        $menu = new LinkedItemCollection();
        foreach ($this->items as $route) {
            $pattern = $route->getPattern();
            if (strpos($pattern, '/') !== false) {
                continue;
            }

            $resource = Url::resource($pattern, []);
            if (!App::access()->isPermitted($resource)) {
                continue;
            }

            try {
                $title = $this->run($resource, 'title');
                if ($title) {
                    $menu->add(new LinkedItem($title, '/' . $resource));
                }
            } catch (Exception $e) {
            }
        }
        return $menu;
    }

    public function forgetByTag($tag)
    {
        foreach ($this->items as $k => $route) {
            if ($route->hasTag($tag)) {
                $route->clearPermission();
                unset($this->items[$k]);
            }
        }
    }

    /**
     * @param string $controller
     * @return string|null
     */
    public function base($controller)
    {
        $controller = trim($controller, "/");
        if (!empty($this->items[$controller])) {
            return $controller;
        }

        $parts = explode("/", $controller);
        for ($i = count($parts) - 2; $i >= 0; $i--) {
            $c = implode("/", array_slice($parts, 0, $i + 1));
            if (isset($this->items[$c])) {
                return $c;
            }
        }
        return null;
    }

    /**
     * @param string $resource
     * @param string $pattern
     * @param array $parsed
     * @return bool
     */
    public function parse($resource, $pattern, &$parsed)
    {
        list($preg, $map) = Url::preg($pattern);
        return $this->pregParse($resource, $preg, $map, $parsed);
    }

    /**
     * @param string $resource
     * @param string $preg
     * @param array $map
     * @param array $parsed
     * @return bool
     */
    public function pregParse($resource, $preg, $map, &$parsed)
    {
        $parsed = [];
        if (preg_match("/^" . $preg . "/si", $resource, $matches)) {
            unset($matches[0]);
            $matches = array_values($matches);
            $parsed = array_combine($map, $matches);
            return true;
        }
        return false;
    }

    /**
     * @param string $resource
     * @param string $pattern
     * @return string
     */
    private function calcDeeperAction($resource, $pattern)
    {
        $deeper = [];
        if ($this->parse($resource, $pattern . "/:id/:__action", $deeper)) {
            $deeperAction = pathinfo($deeper['__action'], PATHINFO_FILENAME);

            return $this->ucfirstEveryWord($deeperAction);
        }

        return '';
    }

    /**
     * @param string $s
     * @return string
     */
    private function ucfirstEveryWord($s)
    {
        $parts = preg_split("/[^\w]/s", $s);
        foreach ($parts as $k => $v) {
            $parts[$k] = ucfirst($v);
        }
        return implode($parts);
    }

}
