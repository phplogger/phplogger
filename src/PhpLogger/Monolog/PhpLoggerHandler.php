<?php
/**
 * Created by PhpStorm.
 * User: Bogdans
 * Date: 11/23/2018
 * Time: 10:12 PM
 */

namespace PhpLogger\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger as MonologLogger;
use PhpLogger\Logger;
use Psr\Log\LogLevel;

class PhpLoggerHandler extends AbstractProcessingHandler
{
    private static $levels = [
        MonologLogger::DEBUG     => LogLevel::DEBUG,
        MonologLogger::INFO      => LogLevel::INFO,
        MonologLogger::NOTICE    => LogLevel::NOTICE,
        MonologLogger::WARNING   => LogLevel::WARNING,
        MonologLogger::ERROR     => LogLevel::ERROR,
        MonologLogger::CRITICAL  => LogLevel::CRITICAL,
        MonologLogger::ALERT     => LogLevel::ALERT,
        MonologLogger::EMERGENCY => LogLevel::EMERGENCY,
    ];
    private $logger;

    /**
     * PhpLoggerHandler constructor.
     * @param string $token
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(string $token, int $level = MonologLogger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->logger = new Logger($token);
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     * @throws \Throwable
     */
    protected function write(array $record)
    {
        $this->logger->log(
            self::$levels[$record['level']] ?? LogLevel::DEBUG,
            $record['message'],
            $record['context']
        );
    }
}