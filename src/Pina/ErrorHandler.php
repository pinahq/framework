<?php


namespace Pina;


class ErrorHandler
{

    public static function handle($errno, $errstr, $errfile, $errline, $errcontext, $backtrace = [])
    {
        if (!(ini_get("error_reporting") & $errno)) {
            return;
        }

        if (ini_get("display_errors") == 0 && ini_get("log_errors") == 0) {
            return;
        }

        # If error has been supressed with an @
        if (error_reporting() == 0) {
            return;
        }

        if (empty($backtrace)) {
            $trace = debug_backtrace();
            array_shift($trace);

            if (is_array($trace) && !empty($trace)) {
                foreach ($trace as $item) {
                    if (!empty($item['file']))
                        $backtrace[] = $item['file'] . ':' . $item['line'];
                }
            }

            if (empty($backtrace)) {
                $backtrace[] = '[empty backtrace]';
            }
        }

        $errortypes = array(
            E_ERROR => "Error",
            E_WARNING => "Warning",
            E_PARSE => "Parse",
            E_NOTICE => "Notice",
            E_CORE_ERROR => "Code Error",
            E_CORE_WARNING => "Code Warning",
            E_COMPILE_ERROR => "Compile Error",
            E_COMPILE_WARNING => "Compile Warning",
            E_USER_ERROR => "User Error",
            E_USER_WARNING => "User Warning",
            E_USER_NOTICE => "User Notice",
            E_STRICT => "Strict",
        );

        $errortype = isset($errortypes[$errno]) ? $errortypes[$errno] : "Unknown Error";

        if (ini_get("display_errors") != 0) {
            echo "$errortype: $errstr in $errfile on line $errline\n";
            echo "Stack trace:\n";
            echo trim(implode("\n", $backtrace))."\n";
        }

        $context = [
            'errortype' => $errortype,
            'errstr' => $errstr,
            'errfile' => $errfile,
            'errline' => $errline,
            'errcontext' => $errcontext,
            'backtrace' => trim(implode("; ", $backtrace)),
        ];

        Log::error('php', $errstr, $context);
    }

    public static function shutdown()
    {
        $error = error_get_last();

        if ($error !== NULL) {
            $errno = $error["type"] ?? E_CORE_ERROR;
            $errfile = $error["file"] ?? "unknown file";
            $errline = $error["line"] ?? -1;
            $errstr = $error["message"] ?? "shutdown";

            $backtrace = [];
            if (preg_match('/Stack trace:\s+(.*)$/si', $errstr, $matches)) {
                $backtrace = explode("\n", trim($matches[1]));
                $errstr = trim(str_replace($matches[0], '', $errstr));
            }

            @header("HTTP/1.1 500 Internal Server Error");

            static::handle($errno, $errstr, $errfile, $errline, [], $backtrace);

            Response::internalError()->send();
        }
    }

}
