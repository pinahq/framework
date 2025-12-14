<?php

namespace Pina;

use Pina\Container\Container;
use Pina\Controls\Control;
use Pina\DB\TriggerUpgrade;
use Pina\Http\Location;
use Pina\Queue\Queue;

class App
{

    private static $config = false;
    private static $layout = null;
    /** @var Container */
    private static $container = null;
    private static $supportedMimeTypes = ['text/html', 'application/json', '*/*'];
    private static $forcedMimeType = null;
    private static $defaultSharedDepencies = [];

    /** @var \Pina\Http\Request[] */
    private static $requestStack = [];

    /**
     * Иницирует приложение
     * @param string $env Режим работы (например, live или test)
     * @param string $configPath Путь к каталогу с настройками
     */
    public static function init($env, $configPath)
    {
        self::env($env);

        Config::init($configPath);
        self::$config = Config::load('app');

        mb_internal_encoding(self::$config['charset']);
        mb_regex_encoding(self::$config['charset']);

        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set(self::$config['timezone']);
        }

        self::$container = new Container;
        self::$container->set('base_url', new Location('/', new \Pina\Http\Url(self::scheme() . "://" . self::host() . "/")));
        if (isset(self::$config['depencies']) && is_array(self::$config['depencies'])) {
            foreach (self::$config['depencies'] as $key => $value) {
                self::$container->set($key, $value);
            }
        }

        self::$config['sharedDepencies'] = Arr::merge(self::$defaultSharedDepencies, self::$config['sharedDepencies']);
        if (isset(self::$config['sharedDepencies']) && is_array(self::$config['sharedDepencies'])) {
            foreach (self::$config['sharedDepencies'] as $key => $value) {
                self::$container->share($key, $value);
            }
        }

        $types = new Container;
        $types->share('string', Types\StringType::class);
        static::$container->share('types', $types);

        static::$container->share('events', new Container());
    }

    /**
     * Возвращает DI контейнер
     * @return Container
     */
    public static function container()
    {
        return self::$container;
    }

    public static function call(Container $env, Callable $fn)
    {
        $back = static::$container;

        $env->addFallback(static::$container);
        static::$container = $env;

        try {
            $fn();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            static::$container = $back;
        }
    }

    public static function cli(): CLI
    {
        return static::load(CLI::class);
    }

    /**
     * Возвращает объект для работы с БД
     */
    public static function db(): DatabaseDriver
    {
        return static::load(DatabaseDriver::class);
    }

    public static function access(): Access
    {
        return static::load(Access::class);
    }

    public static function queue(): Queue
    {
        return static::load(Queue::class);
    }

    /**
     * Возвращает реестр модулей
     * @return ModuleRegistry
     */
    public static function modules()
    {
        return static::load(ModuleRegistry::class);
    }

    public static function event($name): \Pina\Events\EventHandlerRegistry
    {
        /** @var Container $events */
        $events = static::container()->get('events');
        if ($events->has($name)) {
            return $events->get($name);
        }
        $events->share($name, $e = new \Pina\Events\EventHandlerRegistry());
        return $e;
    }

    /**
     * Возвращает DI контейнер
     * @return Container
     */
    public static function types()
    {
        return static::container()->get('types');
    }

    /**
     * @param string $type
     * @return Types\TypeInterface
     * @throws \Pina\Container\NotFoundException
     */
    public static function type($type)
    {
        if ($type instanceof Types\TypeInterface) {
            return $type;
        }

        if (is_array($type)) {
            return static::make(Types\EnumType::class)->setVariants($type);
        }

        $container = static::types();
        $t = $container->load($type);
        if ($t instanceof Types\TypeInterface) {
            return $t;
        }

        throw new \Pina\Container\NotFoundException("Unable to create unsupported class ".$type." as type");
    }

    public static function place($key, ...$params): Place\Place
    {
        static $container = null;

        if (is_null($container)) {
            $container = new Container();
        }

        if (!$container->has($key)) {
            $composer = new Place\PlaceComposer($key);
            $container->share($key, $composer);
        } else {
            /** @var Place\PlaceComposer $composer */
            $composer = $container->load($key);
        }

        return $composer->concrete($params);
    }

    /**
     * Подгружает класс из загрузчика
     */
    public static function make($id)
    {
        return static::container()->make($id);
    }

    /**
     * Подгружает класс из загрузчика
     */
    public static function load($id)
    {
        return static::container()->load($id);
    }

    public static function onLoad($id, Callable $fn)
    {
        static::$container->onLoad($id, $fn);
    }

    /**
     * Возвращает объект роутер
     * @return Router
     */
    public static function router()
    {
        return static::load(Router::class);
    }

    public static function assets(): ResourceManager
    {
        return static::load(ResourceManager::class);
    }

    /**
     * Запускает приложение: анализирует параметры,
     * выбирает и выполняет цепочку контроллеров
     * отрисовывает результат
     */
    public static function run()
    {
        if (self::host() != Input::getHost()) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . App::link($_SERVER['REQUEST_URI']));
            exit;
        }

        $method = Input::getMethod();
        if (!in_array($method, array('get', 'put', 'delete', 'post', 'options'))) {
            @header("HTTP/1.1 501 Not Implemented");
            exit;
        }

        $data = Input::getData();
        if (empty($data[$method]) && !in_array($_SERVER['REQUEST_URI'], array($_SERVER['SCRIPT_NAME'], "", "/"))) {
            $data[$method] = $_SERVER['REQUEST_URI'];
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

        try {
            App::resource($resource);

            $modules = self::modules();
            $modules->load(Config::get('app', 'main') ? Config::get('app', 'main') : \Pina\Modules\App\Module::class);
            $modules->boot('http');

            $resource = DispatcherRegistry::dispatch($resource);

            list($controller, $action, $parsed) = Url::route($resource, $method);
            if (!CSRF::verify($controller, $data)) {
                @header('HTTP/1.1 403 Forbidden');
                exit;
            }

            $response = App::router()->run($resource, $method, $data);
            if ($response instanceof Control) {
                $layout = $response->getLayout();
                $content = $layout->append($response)->drawWithWrappers();
                Response::ok()->setContent($content)->send();
            } elseif ($response instanceof Response) {
                if (!$response->hasContent()) {
                    $content = App::createResponseContent([], $controller, $action);
                    $response->setContent($content);
                }
                $response->send();
            } else {
                throw new NotFoundException;
            }
        } catch (BadRequestException $e) {
            Response::badRequest()->setErrors($e->getErrors())->send();
        } catch (NotFoundException $e) {
            Response::notFound()->send();
        } catch (ForbiddenException $e) {
            Response::forbidden()->send();
        }
    }

    /**
     * Инициализирует обрабатываемый ресурс (единожды во время запуска
     * приложения, повторная инициализация запрещена)
     * Возвращает текущий ресурс
     * @staticvar string $item хранит текущий ресурс
     * @param string $resource ресурс
     * @return string
     */
    public static function resource($resource = '')
    {
        static $item = false;

        if (!empty($resource) && empty($item)) {
            $item = $resource;
        }

        return $item;
    }

    /**
     * Базовый URL на основе настроек схемы и домена приложения
     * @return string
     */
    public static function baseUrl($resource = ''): Location
    {
        /** @var Location $location */
        $location = static::$container->get('base_url');
        if ($resource) {
            return $location->location($resource);
        }
        return $location;
    }

    /**
     * Схема URL приложения
     * @return string
     */
    public static function scheme()
    {
        return isset(self::$config['scheme']) ? self::$config['scheme'] : Input::getScheme();
    }

    /**
     * Домен приложения
     * @return string
     */
    public static function host()
    {
        return isset(self::$config['host']) ? self::$config['host'] : Input::getHost();
    }

    /**
     * Шаблон (скин) приложения
     * @return string
     */
    public static function template()
    {
        return isset(self::$config['template']) ? self::$config['template'] : null;
    }

    /**
     * Путь на диске к папке с приложением
     * @return string
     */
    public static function path()
    {
        return self::$config['path'];
    }

    /**
     * Путь на диске к папке с загрузками (deprecated)
     * @return string
     */
    public static function uploads()
    {
        return self::$config['uploads'];
    }

    /**
     * Кодировка приложения
     * @return string
     */
    public static function charset()
    {
        return self::$config['charset'];
    }

    /**
     * Путь на диске к директории для временных файлов
     * @return string
     */
    public static function tmp()
    {
        return self::$config['tmp'];
    }

    /**
     * Путь на диске к директории кэша шаблонизатора
     * @return string
     */
    public static function templaterCache()
    {
        return self::$config['templater']['cache'];
    }

    /**
     * Путь на диске к директории компилированных данных шаблонизатора
     * @return string
     */
    public static function templaterCompiled()
    {
        return self::$config['templater']['compiled'];
    }

    /**
     * Версия приложения
     * @return string
     */
    public static function version()
    {
        return isset(self::$config['version']) ? self::$config['version'] : '';
    }

    /**
     * Инициализирует и считывает окружение (режим работы) приложения
     * @staticvar string $item хранит режим работы
     * @param string $env режим работы
     * @return string
     */
    public static function env($env = '')
    {
        static $item = false;

        if (!empty($env) && empty($item)) {
            $item = $env;
        }

        return $item;
    }

    /**
     * Генерирует набор параметров для ресурса в момент сбора ссылки
     * @param string $pattern Маска ресурса
     * @param array $params Набор параметров
     * @return string
     */
    public static function getParamsString($pattern, $params)
    {
        $systemParamKeys = array('get', 'app', 'anchor');

        foreach ($params as $k => $v) {
            if (strpos($pattern . '/', ':' . $k . '/') !== false || in_array($k, $systemParamKeys)) {
                unset($params[$k]);
            }
        }

        return http_build_query($params);
    }

    /**
     * Формирует ссылку по заданной маске
     * @param string $pattern Маска ссылки
     * @param array $params Параметры
     * @return string
     */
    public static function link($pattern, $params = array(), $baseUrl = null)
    {
        return self::baseUrl($baseUrl)->link($pattern, $params);
    }

    /**
     * Принудительно задает mime-тип, который должно вернуть приложение
     * @param string $mime
     */
    public static function forceMimeType($mime)
    {
        static::$forcedMimeType = $mime;
    }

    /**
     * Вычисляет mime-тип, который должно вернуть приложение
     * @return string
     */
    public static function negotiateMimeType()
    {
        if (!empty(static::$forcedMimeType)) {
            return static::$forcedMimeType;
        }

        $acceptTypes = [];

        $accept = strtolower(str_replace(' ', '', isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : ''));
        $accept = explode(',', $accept);
        foreach ($accept as $a) {
            $q = 1;
            if (strpos($a, ';q=')) {
                list($a, $q) = explode(';q=', $a);
            }
            $acceptTypes[$a] = $q;
        }
        arsort($acceptTypes);

        if (!static::$supportedMimeTypes) {
            $keys = array_keys($acceptTypes);
            return reset($keys);
        }

        $supported = array_map('strtolower', static::$supportedMimeTypes);

        foreach ($acceptTypes as $mime => $q) {
            if ($q && in_array($mime, $supported)) {
                return $mime;
            }
        }
        return 'text/html';
    }

    /**
     * Инстанцирует контент в соответствии с mime-типом приложения
     * @param mixed $results Результат выполнения запроса
     * @param string $controller Контроллер
     * @param string $action Метод контроллера
     * @return ContentInterface
     */
    public static function createResponseContent($results, $controller, $action)
    {
        $mime = static::negotiateMimeType();
        switch ($mime) {
            case 'application/json':
            case 'text/json':
                return new JsonContent($results);
        }

        return $results;
    }

    /**
     * Предлагает набор обновлений для структуре БД
     * @return array
     */
    public static function getUpgrades()
    {
        $firstUpgrades = array();
        $lastUpgrades = array();
        $triggers = array();
        App::walkModuleClasses(
            'Gateway',
            function (TableDataGateway $gw) use (&$firstUpgrades, &$lastUpgrades, &$triggers) {
                list($first, $last) = $gw->getUpgrades();
                $firstUpgrades = array_merge($firstUpgrades, $first);
                $lastUpgrades = array_merge($lastUpgrades, $last);
                $triggers = array_merge($triggers, $gw->getTriggers());
            }
        );

        $upgrades = array_merge($firstUpgrades, $lastUpgrades, TriggerUpgrade::getUpgrades($triggers));

        return $upgrades;
    }

    /**
     * Обходит классы, с заданным суффиксом и выполняет для каждого заданную
     * функцию-обработчик
     * @param string $type Суффикс имени класса
     * @param callable $callback Функция, которую необходимо вызывать с объектами найденных классов в виде параметра
     */
    public static function walkModuleRootClasses($type, $callback)
    {
        $paths = self::modules()->getPaths();
        $suffix = $type . '.php';
        $suffixLength = strlen($suffix);
        foreach ($paths as $ns => $path) {
            $files = array_filter(scandir($path), function ($s) use ($suffix, $suffixLength) {
                return strrpos($s, $suffix) === (strlen($s) - $suffixLength);
            });

            foreach ($files as $file) {
                $className = $ns . '\\' . pathinfo($file, PATHINFO_FILENAME);
                try {
                    $c = new $className;
                    $callback($c);
                } catch (\Throwable $e) {
                }
            }
        }
    }

    /**
     * Обходит классы, с заданным суффиксом и выполняет для каждого заданную
     * функцию-обработчик
     * @param string $type Суффикс имени класса
     * @param callable $callback Функция, которую необходимо вызывать с объектами найденных классов в виде параметра
     */
    public static function walkModuleClasses(string $type, callable $callback)
    {
        static::walkModuleClassNames($type, function($className) use ($callback) {
            try {
                $c = new $className;
                $callback($c);
            } catch (\Throwable $e) {
            }
        });
    }

    /**
     * Обходит именна классов, с заданным суффиксом и выполняет для каждого заданную
     * функцию-обработчик
     * @param string $type Суффикс имени класса
     * @param callable $callback Функция, которую необходимо вызывать с объектами найденных классов в виде параметра
     */
    public static function walkModuleClassNames(string $type, callable $callback)
    {
        $paths = self::modules()->getPaths();
        foreach ($paths as $ns => $path) {
            static::walkClassNamesInPath($ns, $path, $type, $callback);
        }
    }

    public static function walkClassNamesInPath(string $ns, string $path, string $type, callable $callback)
    {
        $suffix = $type . '.php';
        $suffixLength = strlen($suffix);
        $allFiles = scandir($path);
        $toWalk = array_filter($allFiles, function ($s) use ($suffix, $suffixLength) {
            return strrpos($s, $suffix) === (strlen($s) - $suffixLength);
        });

        foreach ($toWalk as $file) {
            $className = $ns . '\\' . pathinfo($file, PATHINFO_FILENAME);
            $callback($className);
        }

        $paths = array_filter($allFiles, function ($s) use ($path) {
            return $s[0] >= 'A' && $s[0] <= 'Z' && is_dir($path . '/' . $s);
        });

        foreach ($paths as $file) {
            static::walkClassNamesInPath(
                $ns . '\\' . pathinfo($file, PATHINFO_FILENAME),
                $path . '/' . $file,
                $type,
                $callback
            );
        }
    }

    public static function pushRequest(\Pina\Http\Request $request)
    {
        array_push(static::$requestStack, $request);
    }

    public static function getActualRequest(): \Pina\Http\Request
    {
        $top = count(static::$requestStack) - 1;
        if ($top < 0) {
            throw new \Exception('wrong router stack state');
        }
        return static::$requestStack[$top];
    }

    public static function popRequest()
    {
        array_pop(static::$requestStack);
    }
}

function __($string)
{
    return Language::translate($string, __NAMESPACE__);
}
