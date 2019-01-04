<?php

namespace PhpLoggerTest;

use PhpLogger\PhpLogger;
use PHPUnit\Framework\TestCase;

class DefaultTest extends TestCase
{
    /**
     * @test
     */
    public function testOhMy()
    {
        $logger = new PhpLogger('nauidbn2nndi21nd2ju');
        $logger->debug('fuck you, Clyde!');

        $this->assertTrue(true);
    }
}
