<?php
/**
 * Created by PhpStorm.
 * User: Bogdans
 * Date: 8/5/2018
 * Time: 2:42 PM
 */

namespace PhpLogger;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Stream;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Logger implements LoggerInterface
{
    /** @var int  */
    private static $sequence = 0;
    /** @var string */
    private $version = '1.2';
    /** @var string  */
    private $sharedFileSystemSpace;
    /** @var string  */
    private $lastFailureFileName = 'phplogger-last-failure';
    /** @var string  */
    private $url = 'https://api.phplogger.com';
    /** @var string */
    private $token;
    /** @var string  */
    private $id;
    /** @var Client  */
    private $client;
    /** @var resource  */
    private $bufferStream;
    /** @var Stream  */
    private $buffer;
    /** @var int  */
    private $bufferThreshold = 4194304; // bytes (4mb) 1048576 * 4
    /** @var bool  */
    private $wasOpened = false;
    /** @var bool  */
    private $wasClosed = false;

    /**
     * PhpLogger constructor.
     * @param string $token
     */
    public function __construct($token)
    {
        $this->client = new Client(
            [
                'base_uri' => $this->url,
                'timeout' => 5
            ]
        );
        $this->token = $token;
        $this->id = $this->generateId();
        $this->sharedFileSystemSpace = $this->findOptimalSharedFileSystem();
    }

    /**
     * Opens the buffer and logging capabilities
     * If it was not yet opened
     */
    private function openIfNot()
    {
        if ($this->wasClosed === true) {
            throw new \RuntimeException(
                'Cannot open buffer for PhpLogger. The buffer has been closed and send off to the server already.'
            );
        }

        if ($this->wasOpened === true) {
            return;
        }

        $this->open();
        $this->wasOpened = true;
    }

    /**
     * Opens the buffer and logging capabilities
     */
    private function open()
    {
        $this->bufferStream = fopen('php://temp', 'r+'); // up to 2mb in memory
        $this->buffer = new Stream($this->bufferStream);
        register_shutdown_function([$this, 'closeIfNot']);
    }

    /**
     * Sends off all the logs that are in the buffer
     * And releases the buffer
     * NOTE: After calling this method the logger object is not able to send logs anymore
     * @throws \Throwable
     */
    public function closeIfNot()
    {
        if ($this->wasClosed === true || $this->wasOpened === false) {
            return;
        }
        $this->close();
        $this->wasClosed = true;
    }

    /**
     * Ends the logging and discards the buffer
     * @throws \Throwable
     */
    private function close()
    {
        if ($this->buffer->tell() > 0) {
            $this->bufferSend();
        }
        unset($this->buffer);
    }

    /**
     * Returns current logger process ID
     *
     * @return string
     */
    public function __toString()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    private function generateId()
    {
        $microTime = explode(' ', microtime());
        $microTimeSeconds36 = base_convert($microTime[1], 10, 36);
        $microTimeMicroSeconds36 = base_convert(substr($microTime[0], 2), 10, 36);
        return implode('.',
            [
                $microTimeSeconds36,
                $microTimeMicroSeconds36,
                getmypid(),
                self::$sequence++,
            ]
        );
    }

    /**
     * @return array
     */
    private function headers()
    {
        return [
            'X-PHP-LOGGER-TOKEN' => $this->token,
            'X-PHP-LOGGER-ID' => $this->id,
            'X-PHP-LOGGER-HOSTNAME' => gethostname(),
            'X-PHP-LOGGER-FILENAME' => array_key_exists("SCRIPT_FILENAME", $_SERVER) ? $_SERVER["SCRIPT_FILENAME"] : '',
            'X-PHP-LOGGER-URI' => array_key_exists("REQUEST_URI", $_SERVER) ? $_SERVER["REQUEST_URI"] : '',
            'X-PHP-LOGGER-START' => array_key_exists("REQUEST_TIME", $_SERVER) ? $_SERVER["REQUEST_TIME"] : '',
            'X-PHP-LOGGER-SAPI' => php_sapi_name(),
            'X-PHP-LOGGER-HOST' => array_key_exists('HTTP_HOST', $_SERVER) ? $_SERVER['HTTP_HOST'] : '',
        ];
    }

    /**
     * @param string $body
     */
    private function bufferStore($body)
    {
        $this->buffer->write($body . "\n");
    }

    /**
     * Send buffer to the server
     * @throws \Throwable
     */
    private function bufferSend()
    {
        $secondsSinceLastFailure = time() - $this->sharedMemoryReadLastFailure();
        // when cannot connect to server - take a break from sending logs
        if ($secondsSinceLastFailure < 60) {
            return;
        }
        // try sending the logs
        try {
            $this->client->put(
                '/input/' . $this->version,
                [
                    'headers' => $this->headers(),
                    'body' => $this->buffer
                ]
            );
        } catch (\Throwable $exception) {
            // handle authentication failure
            if ($exception->getCode() === 403) {
                throw $exception;
            }
            // silence the rest
            $this->sharedMemorySaveLastFailure();
        }
    }

    /**
     * Clear buffer for future use
     */
    private function bufferClear()
    {
        rewind($this->bufferStream);
        ftruncate($this->bufferStream, 0);
    }

    /**
     * @return string
     */
    private function findOptimalSharedFileSystem()
    {
        $sharedMemory = '/dev/shm';
        if (is_dir($sharedMemory) === true && is_writable($sharedMemory) === true) {
            return $sharedMemory;
        }
        $sharedTemporarySpace = sys_get_temp_dir();
        if (is_dir($sharedTemporarySpace) === true && is_writable($sharedTemporarySpace) === true) {
            return $sharedTemporarySpace;
        }
        throw new \RuntimeException(
            sprintf(
                'Could not find shared file system path. Both %s and %s are not available. Please make one of the directories available.',
                $sharedMemory,
                $sharedTemporarySpace
            )
        );
    }

    /**
     * @return string|null
     */
    private function sharedMemoryReadLastFailure()
    {
        $path = $this->sharedFileSystemSpace . '/' . $this->lastFailureFileName;
        if (is_file($path) === false) {
            return null;
        }
        $lastFailure = file_get_contents($path);
        return $lastFailure ? (string) $lastFailure : null;
    }

    /**
     * @return bool
     */
    private function sharedMemorySaveLastFailure()
    {
        $path = $this->sharedFileSystemSpace . '/' . $this->lastFailureFileName;
        return file_put_contents($path, time()) === false ? false : true;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     *
     * @return void
     * @throws \Throwable
     */
    public function log($level, $message, array $context = array())
    {
        $this->openIfNot();

        $body = json_encode(
            [
                'severity' => (string) $level,
                'message' => $message,
                'context' => $context,
                'stamp' => microtime(true),
            ]
        );
        $this->bufferStore($body);

        if ($this->buffer->tell() <= $this->bufferThreshold) {
            return;
        }

        $this->bufferSend();
        $this->bufferClear();
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     * @throws \Throwable
     */
    public function emergency($message, array $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     * @throws \Throwable
     */
    public function alert($message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     * @throws \Throwable
     */
    public function critical($message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     * @throws \Throwable
     */
    public function error($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     * @throws \Throwable
     */
    public function warning($message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     * @throws \Throwable
     */
    public function notice($message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     * @throws \Throwable
     */
    public function info($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     * @throws \Throwable
     */
    public function debug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}