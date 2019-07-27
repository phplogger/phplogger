<?php


namespace PhpLogger;


use PhpLogger\Format\Format;

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

    private static function registerExceptionHandler(Logger $logger)
    {
        set_exception_handler(
            self::createExceptionHandlerCallback($logger)
        );
    }

    private static function createErrorHandlerCallback(Logger $logger)
    {
        return function(int $errno, string $errstr, string $errfile, int $errline) use ($logger) {
            $loggable = Format::createFromHandledError($errno, $errstr, $errfile, $errline);
            $logger->error($loggable->toString(), $loggable->toArray());
        };
    }

    private static function createExceptionHandlerCallback(Logger $logger)
    {
        return function(\Throwable $exception) use ($logger) {
            $loggable = Format::createFromException($exception);
            $logger->error($loggable->toString(), $loggable->toArray());
        };
    }
}