# PHP Logger
PhpLogger is a logging system, designed to have be easy to install as use. 
Our approach is centered around the idea that application logs should be easy to acquire, 
access and use in order to provide quality software. We encourage you to log everything your 
application does and we provide the tools for that.

## Installation

You need an account first, which can be created at <a href="https://phplogger.com">phplogger.com</a>.

The basic installation requires <a href="https://getcomposer.org/download/">composer</a> to be installed on the system.

```shell
composer require phplogger/phplogger
```

## Usage

PHP Logger client supports PHP <a href="https://www.php-fig.org/psr/psr-3/#3-psrlogloggerinterface">PSR-3 standard</a>.

### Creating logger object

You need to create PHP Logger object
```php
# add autoloader
include __DIR__ . '/vendor/autoload.php';
# create logger object
$token = 'd173f174f8aa6793ab8f8b6c9286ec9834a33ee6';
$logger = new \PhpLogger\Logger($token);
```

NOTE: token can be acquired at the PHP Logger <a href="https://phplogger/profile">profile page</a>.

### Writing logs

After you created the object writing logs is pretty simple.

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

Also you can add context array to the messages, which will be passed to the server as well
```php
# define context to pass along with the message
$context = ['time' => microtime(true)];
# write log message with context
$logger->warning('this is a warning message', $context);
```

## Delivery mechanisms

Each PHP Logger object contains a buffer which collects all of the logs into memory.
When the buffer size is exceeded or the PHP script execution ends, the data gets transferred to the server.
If the data transfer fails, the logs are completely discarded. 
There are additional mechanisms that prevent logs from being send to a faulty server, which protects the application
from performance degradation. 

## Fallback mechanism

Whenever the library is unable to reach the server in a reasonable amount of time, the fallback mechanism are initiated 
with the purpose of preventing application overall performance degradation. The library first checks if shared memory 
directory (/dev/shm) is available, in case it's not - the fallback to temporary directory happens. The shared file system 
space is used to record last error and prevent the library of spamming the non-responding server. Basically it takes a 
1 minute timeout and then tries to send logs again. 

## Support

In case of ANY issues with the library please create an Issue on GitHub 
or contact by email <a href="mailto:support@phplogger.com">support@phplogger.com</a>.