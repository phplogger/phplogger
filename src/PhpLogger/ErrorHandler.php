<?php


namespace PhpLogger;


class ErrorHandler
{
    public static function register(Logger $logger)
    {
        self::registerExceptionHandler($logger);
        self::registerErrorHandler($logger);
    }

    private static function registerErrorHandler(Logger $logger)
    {
        set_error_handler(
            self::createErrorHandlerCallback($logger)
        );
    }

    private static function createErrorHandlerCallback(Logger $logger)
    {
        return function(int $errno, string $errstr, string $errfile, int $errline) use ($logger) {
            $message = 'Encountered Error with number ' . $errno;
            $message .= "\r\n";
            $message .= $errstr;
            $logger->error(
                $message,
                [
                    'message' => $errstr,
                    'code' => $errno,
                    'file' => $errfile,
                    'line' => $errline,
                ]
            );
        };
    }

    private static function registerExceptionHandler(Logger $logger)
    {
        set_error_handler(
            self::createExceptionHandlerCallback($logger)
        );
    }

    private static function createExceptionHandlerCallback(Logger $logger)
    {
        return function(\Throwable $exception) use ($logger) {
            $message = 'Encountered Exception of class ' . get_class($exception);
            $message .= "\r\n";
            $message .= $exception->getMessage();
            $message .= "\r\n";
            $message .= 'Trace route';
            $message .= $exception->getTraceAsString();
            $logger->error(
                $message,
                [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => array_map(
                        function(array $traceRecord) {
                            return [
                                'file' => $traceRecord['file'],
                                'line' => $traceRecord['line'],
                                'function' => $traceRecord['function'],
                                'class' => $traceRecord['class'],
                                'type' => $traceRecord['type'],
                                'args_count' => count($traceRecord['args']),
                            ];
                        },
                        $exception->getTrace()
                    )
                ]
            );
        }
    }

}