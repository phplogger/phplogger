<?php


namespace PhpLogger\Format;


class Format
{
    public static function createFromException(\Throwable $exception): Loggable
    {
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
        $context = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => array_map(
                function(array $traceRecord) {
                    return [
                        'file' => $traceRecord['file'],
                        'line' => $traceRecord['line'],
                        'function' => array_key_exists('function', $traceRecord)
                            ? $traceRecord['function']
                            : null,
                        'class' => array_key_exists('class', $traceRecord)
                            ? $traceRecord['class']
                            : null,
                        'type' => array_key_exists('type', $traceRecord)
                            ? $traceRecord['type']
                            : null,
                        'args_count' => array_key_exists('args', $traceRecord)
                            ? count($traceRecord['args'])
                            : 0,
                    ];
                },
                $exception->getTrace()
            )
        ];
        return new Loggable($message, $context);
    }

    public static function createFromHandledError(int $errno, string $errstr, string $errfile, int $errline)
    {
        $message = 'In ' . $errfile . ' line ' . $errline . ':';
        $message .= "\r\n";
        $message .= "\r\n";
        $message = '[' . $errno . ']';
        $message .= "\r\n";
        $message .= $errstr;
        $context = [
            'message' => $errstr,
            'code' => $errno,
            'file' => $errfile,
            'line' => $errline,
        ];
        return new Loggable($message, $context);
    }
}