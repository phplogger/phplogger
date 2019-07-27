# Phplogger
PhpLogger - logging system, which is designed to get insights from your application data. It includes logs collection, process tracking and pivot table event exploration. We encourage you to log everything your application does and we provide the tools for that.

[![CircleCI](https://circleci.com/gh/phplogger/phplogger.svg?style=shield)](https://circleci.com/gh/phplogger/phplogger)
[![MIT Licence](https://badges.frapsoft.com/os/mit/mit.svg?v=103)](https://opensource.org/licenses/mit-license.php)

## Installation

You need an account first, which can be created at <a href="https://phplogger.com">phplogger.com</a>.

The basic installation requires <a href="https://getcomposer.org/download/">composer</a> to be installed on the system.

```shell
composer require phplogger/phplogger
```

## Initialize $logger
You need to create PHP Logger object
```php
# add autoloader
include __DIR__ . '/vendor/autoload.php';
# create logger object
$token = 'd173f174f8aa6793ab8f8b6c9286ec9834a33ee6';
$logger = new \PhpLogger\Logger($token);
```
Phplogger client supports <a href="https://www.php-fig.org/psr/psr-3/#3-psrlogloggerinterface">PSR-3 standard</a>.

NOTE: token can be acquired at the PHP Logger <a href="https://app.phplogger.com/profile/product-setup">first setup</a> tutorial.

## Collect logs


Call methods with appropriate severity. All of the severity methods support $message and $context arguments.

```php
# write log messages
$logger->emergency('this is an emergency message line');
$logger->alert('this is an alert message line');
$logger->critical('this is a critical message line');
$logger->error('this is an error message line');
$logger->warning('this is a warning message line');
$logger->notice('this is a notice message line');
$logger->info('this is an info message line');
$logger->debug('this is a debug message line');
```

Pass context array to the methods, which will be passed to the server as well
```php
# define context to pass along with the message
$context = ['time' => microtime(true)];
# write log message with context
$logger->warning('this is a warning message', $context);
```

## Catch errors

Phplogger has an error handler class which allows to register $logger object to catch and log errors.
This will catch both PHP Errors and Unhandled Exceptions.
```php
# register $logger for error collection
\PhpLogger\ErrorHandler::register($logger);
```
NOTE: It's recomender to register only one $logger object for error collection

## Integrations

Phplogger supports integration with multiple frameworks and logging approaches. For more information see the 
<a href="https://phplogger.com/learn">learning page</a>.

## Logs Delivery

Each $logger object contains a buffer which collects all of the logs into memory.
When the buffer size is exceeded or the PHP script execution ends, the data gets transferred to the server.
In case the data transfer fails - the buffer is discarded. 
There are additional mechanisms that prevent logs from being send to a faulty server. They protect the PHP application
from performance degradation. 
 
## Support

In case of ANY issues with the library please create an Issue on GitHub 
or contact by email <a href="mailto:support@phplogger.com">support@phplogger.com</a>.
