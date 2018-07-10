<?php

namespace Pina;

use League\CLImate\CLImate;

class CLI
{

    private static $driver = null;

    public static function handle($argv, $scriptName)
    {
        self::$driver = new CLImate;
        
        CLI::info('Hello from Pina framework shell');

        list($cmd, $data) = self::parseParams($argv, $scriptName);
        
        $modules = App::container()->get(ModuleRegistryInterface::class);
        $modules->boot('cli');

        $parts = explode(".", $cmd);
        if (count($parts) !== 2) {
            CLI::error("Bad command");
            exit;
        }

        list($group, $action) = $parts;
        
        $owner = Route::owner($group);
        if (empty($owner)) {
            CLI::error("Such command has not been found");
            exit;
        }
        
        CLI::info("Affected module: ".$owner->getTitle());

        $path = $owner->getPath();

        if (!file_exists($path."/cli/".$group."/".$action.".php")) {
            CLI::error("Command '".$cmd."' does not exist");
            exit;
        }
        CLI::border('-');

        include $path."/cli/".$group."/".$action.".php";

        CLI::border('-');
        CLI::info("Memory Usage: ".round(memory_get_peak_usage()/1024/1024, 3)."M");

    }

    private static function parseParams($argv, $scriptName)
    {
        $cmd = '';
        $data = array();
        if (is_array($argv)) {
            foreach ($argv as $v) {
                if (strpos($v, $scriptName) !== false) {
                    continue;
                }

                if (empty($cmd)) {
                    $cmd = $v;
                    continue;
                }

                $param = explode('=', $v);
                if (count($param) != 2) {
                    continue;
                }

                $data[$param[0]] = $param[1];
            }
        }
        
        return array($cmd, $data);
    }

    public static function __callStatic($name, $arguments)
    {
        try {
            return call_user_func_array(array(self::$driver, $name), $arguments);
        } catch (\Exception $e) {
            die('Method CLI::' . $name . ' does not exist' . "\r\n");
        }
    }

}
