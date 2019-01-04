<?php
/**
 * Created by PhpStorm.
 * User: Bogdans
 * Date: 11/23/2018
 * Time: 10:12 PM
 */

namespace PhpLogger\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use PhpLogger\PhpLogger;
use Psr\Log\LogLevel;

class PhpLoggerHandler extends AbstractProcessingHandler
{
    private static $levels = [
        Logger::DEBUG     => LogLevel::DEBUG,
        Logger::INFO      => LogLevel::INFO,
        Logger::NOTICE    => LogLevel::NOTICE,
        Logger::WARNING   => LogLevel::WARNING,
        Logger::ERROR     => LogLevel::ERROR,
        Logger::CRITICAL  => LogLevel::CRITICAL,
        Logger::ALERT     => LogLevel::ALERT,
        Logger::EMERGENCY => LogLevel::EMERGENCY,
    ];
    private $logger;

    /**
     * PhpLoggerHandler constructor.
     * @param string $token
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(string $token, int $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);

        $this->logger = new PhpLogger($token);
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
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