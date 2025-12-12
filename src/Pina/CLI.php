<?php

namespace Pina;

class CLI
{

    protected $aliases = [];

    public function register($alias, $class)
    {
        $this->aliases[$alias] = $class;
    }

    protected function resolve($cmd): ?Command
    {
        $cmd = $this->aliases[$cmd] ?? $cmd;

        if (!empty($cmd) && class_exists($cmd) && is_subclass_of($cmd, Command::class)) {
            return App::load($cmd);
        }

        return null;
    }

    public function run($argv)
    {
        while ($cmd = array_shift($argv)) {
            $command = $this->resolve($cmd);
            if (empty($command)) {
                continue;
            }

            $input = trim(implode(' ', $argv));

            try {
                list($msec, $sec) = explode(' ', microtime());
                $startTime = (float)$msec + (float)$sec;

                $command($input);
                $output = $command($input);

                list($msec, $sec) = explode(' ', microtime());
                $totalTime = (float)$msec + (float)$sec - $startTime;
                $memory = floor(memory_get_peak_usage() / 1000000);

                $context = [
                    'cmd' => $cmd,
                    'input' => $input,
                    'output' => $output,
                    'time' => $totalTime,
                    'memory_peak' => $memory . 'M',
                ];
                Log::info('cli', $command->__toString() . ": " . $output, $context);
                echo $output . "\n";
                echo $command->__toString() . ' ' . round($totalTime, 4) . 's ' . $memory . 'M done.' . "\n";

            } catch (\Exception $e) {
                Log::error('cli', $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), $e->getTrace());
                echo $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n";
                echo $command->__toString() . ' failed.' . "\n";
            }

            break;
        }
    }
}