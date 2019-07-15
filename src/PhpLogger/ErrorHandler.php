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
            $message = 'In ' . $errfile . ' line ' . $errline . ':';
            $message .= "\r\n";
            $message .= "\r\n";
            $message = '[' . $errno . ']';
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
        set_exception_handler(
            self::createExceptionHandlerCallback($logger)
        );
    }

    private static function createExceptionHandlerCallback(Logger $logger)
    {
        return function(\Throwable $exception) use ($logger) {
            $message = 'In ' . $exception->getFile() . ' line ' . $exception->getLine() . ':';
            $message .= "\r\n";
            $message .= "\r\n";
            $message .= '[' . get_class($exception) . ']';
            $message .= "\r\n";
            $message .= $exception->getMessage();
            $message .= "\r\n";
            $message .= "\r\n";
            $message .= 'Exception trace:';
            $message .= "\r\n";
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
        };
    }
}