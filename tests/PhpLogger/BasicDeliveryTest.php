<?php

namespace PhpLogger;

use PHPUnit\Framework\TestCase;
use Throwable;

class BasicDeliveryTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testMessageDelivery()
    {
        // create logger
        $logger = new Logger(getenv('PHPLOGGER_TOKEN'));

        // write messages
        $logger->emergency('this is an emergency message line');
        $logger->alert('this is an alert message line');
        $logger->critical(
            'this is a critical message line',
            [ 'optional' => 'context', ]
        );
        $logger->error('this is an error message line');
        $logger->warning('this is a warning message line');
        $logger->notice('this is a notice message line');
        $logger->info('this is an info message line');
        $logger->debug('this is a debug message line');

        // trigger logs delivery
        $logger->closeIfNot();

        // make sure no exceptions are thrown
        $this->assertTrue(true);
    }

    /**
     * @throws Throwable
     */
    public function testProperClosure()
    {
        // create logger
        $logger = new Logger(getenv('PHPLOGGER_TOKEN'));

        // write messages
        $logger->emergency('this is an emergency message line');
        $logger->alert('this is an alert message line');

        // trigger logs delivery
        $logger->closeIfNot();

        // should not happen
        try {
            $logger->alert('this is an alert message line');
        } catch (\RuntimeException $exception) {
            $this->assertEquals(
                'Cannot open buffer for PhpLogger. The buffer has been closed and send off to the server already.',
                $exception->getMessage()
            );
        }
    }
}
