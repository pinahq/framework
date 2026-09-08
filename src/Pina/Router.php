<?php

namespace Pina;

use Exception;
use Pina\Http\Request;
use Pina\Model\LinkedItem;
use Pina\Model\LinkedItemCollection;
use Pina\Router\DispatcherInterface;
use Pina\Router\RouteGroup;
use Pina\Router\RouteLocator;

class Router extends RouteGroup
{

    /** @var DispatcherInterface[] */
    protected $dispatchers = [];

    public function registerDispatcher(DispatcherInterface $dispatcher)
    {
        $this->dispatchers[] = $dispatcher;
    }


    public function handle()
    {
//        if (App::host() != Input::getHost()) {
//            header('HTTP/1.1 301 Moved Permanently');
//            header('Location: ' . App::link($_SERVER['REQUEST_URI']));
//            exit;
//        }

        $method = Input::getMethod();
        if (!in_array($method, array('get', 'put', 'delete', 'post', 'options'))) {
            @header("HTTP/1.1 501 Not Implemented");
            exit;
        }

        $resource = Input::getResource();

        //TODO: get these paths based on config
        $staticFolders = array('cache/', 'static/', 'uploads/', 'vendor/');
        foreach ($staticFolders as $folder) {
            if (strncasecmp($resource, $folder, strlen($folder)) === 0) {
                @header('HTTP/1.1 404 Not Found');
                exit;
            }
        }

        $mime = App::negotiateMimeType();
        if (empty($mime)) {
            @header('HTTP/1.1 406 Not Acceptable');
            exit;
        }

        if (!CSRF::verify()) {
            Response::forbidden()->send();
            return;
        }

        try {
            $this->run($this->dispatch($resource), $method, Input::getData());
        } catch (BadRequestException $e) {
            Response::badRequest()->setErrors($e->getErrors())->send();
        } catch (NotFoundException $e) {
            Response::notFound()->send();
        } catch (ForbiddenException $e) {
            Response::forbidden()->send();
        }
    }

    /**
     * @param string $resource
     * @param string $method
     * @return bool
     */
    public function exists($resource, $method)
    {
        if (!$this->isPermitted($resource)) {
            return null;
        }

        list($controller, $action, $params) = Url::route($resource, $method);

        $locator = $this->locateRoute($controller);
        if ($locator === null) {
            return false;
        }

        $action .= $this->calcDeeperAction($resource, $locator->getRoute()->getPattern());
        return $locator->getRoute()->exists($action);
    }

    /**
     *
     * @param string $resource
     * @param string $method
     * @param array $data
     * @return mixed
     * @throws Container\NotFoundException
     */
    public function call($resource, $method, $data = [])
    {
        if (!$this->isPermitted($resource)) {
            return null;
        }

        $r = null;
        $this->locate($resource, $method, $data, function(RouteLocator $locator, $action, $params) use (&$r) {
            $r = $locator->callRoute($action, $params);
        });
        return $r;
    }

    protected function run($resource, $method, $data = [])
    {
        if (!$this->isPermitted($resource)) {
            Response::forbidden()->send();
            return;
        }

        $this->locate($resource, $method, $data, function(RouteLocator $locator, $action, $params) {
            $locator->runRoute($action, $params);
        });
    }

    protected function locate($resource, $method, $data, callable $fn)
    {
        list($controller, $action, $params) = Url::route($resource, $method);
        $locator = $this->locateRoute($controller);
        if ($locator === null) {
            return;
        }

        App::pushRequest($this->makeRequest($resource, $locator->getRoute()->getController(), $data, $locator->getRoute()->getPattern()));
        try {
            $action .= $this->calcDeeperAction($resource, $locator->getRoute()->getPattern());
            $fn($locator, $action, $params);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            App::popRequest();
        }
    }

    /**
     * @param string $resource
     * @param string $pattern
     * @return string
     */
    public function calcDeeperAction($resource, $pattern)
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


    protected function makeRequest($resource, $c, $data, $pattern)
    {
        $parsed = [];
        $this->parse($resource, $pattern, $parsed);
        unset($parsed['']);

        $request = new Request($_GET, $data, $parsed, $_COOKIE, $_FILES, $_SERVER);

        $location = App::location($resource);

        $controllerCount = count(explode('/', $c));
        $amount = $controllerCount * 2 - 1;

        $baseResource = implode('/', array_slice(explode('/', trim($resource, '/')), 0, $amount));

        $base = App::location($baseResource);

        $request->setLocation($base, $location);

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
                $title = $this->call($resource, 'title');
                if ($title) {
                    $menu->add(new LinkedItem($title, '/' . $resource));
                }
            } catch (Exception $e) {
            }
        }
        return $menu;
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

}