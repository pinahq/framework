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

    /** @var \Pina\Http\Request[] */
    private static $requestStack = [];

    /**
     * Иницирует приложение
     * @param string $env Режим работы (например, live или test)
     * @param string $configPath Путь к каталогу с настройками
     */
    public static function init($configPath)
    {
        Config::init($configPath);
        self::$config = Config::load('app');

        mb_internal_encoding(self::$config['charset']);
        mb_regex_encoding(self::$config['charset']);

        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set(self::$config['timezone']);
        }

        self::$container = new Container;
        self::$container->set('base_url', new Location('/', new \Pina\Http\Url(self::scheme() . "://" . self::host() . "/")));

        static::$container->share('types', new Container());
        static::$container->share('events', new Container());

        if (Config::get('app', 'main')) {
            App::modules()->load(Config::get('app', 'main'));
        }
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
     * Версия приложения
     * @return string
     */
    public static function version()
    {
        return isset(self::$config['version']) ? self::$config['version'] : '';
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
