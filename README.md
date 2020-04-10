# websocket-server-adapter-amphp
Adapter of aerys websocket server
# Installation
This package can be installed as a Composer dependency.

`composer require ekstazi/websocket-server-adapter-amphp`
# Requirements
PHP 7.2+
# Usage
## With container
If you have container then add this to your `container.php`
```php
use Amp\Http\Server\Options;
use ekstazi\websocket\server\amphp\FactoryBuilder;
use ekstazi\websocket\server\ServerFactory;

// ....

return [
    ServerFactory::class => new FactoryBuilder(),
    // this is optional config for default options to connections
    "config" => [
        "websocket" => [
            'serverOptions' => new Options(),
        ]
    ]
];
```
Then in your code:
```php

use Amp\Promise;
use Amp\Success;
use Amp\Socket\Server;
use ekstazi\websocket\server\Connection;
use ekstazi\websocket\server\ConnectionInfo;
use ekstazi\websocket\server\Handler;
use ekstazi\websocket\server\ServerFactory;
use \Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
/** @var ServerFactory $factory */

$factory = $container->get(ServerFactory::class);
$server = $factory->create(Server::listen('tcp://127.0.0.1:8000'));

$server->addRoute('/ws', new class() implements Handler {
    public function onHandshake(ConnectionInfo $connectionInfo): Promise {
     return new Success();
    }
    public function handle(Connection $connection): Promise {
     return new Success();
    }
    public function getSubProtocols() : array{
     return [];
    }
});

$server->run();
```

## Without container
```php
use Amp\Promise;
use Amp\Success;
use Amp\Socket\Server;
use ekstazi\websocket\server\amphp\ServerFactory;
use ekstazi\websocket\server\Connection;
use ekstazi\websocket\server\ConnectionInfo;
use ekstazi\websocket\server\Handler;

/** @var ServerFactory $factory */
$factory = new ServerFactory(new \Amp\Http\Server\Options());
$server = $factory->create(Server::listen('tcp://127.0.0.1:8000'));

$server->addRoute('/ws', new class() implements Handler {
    public function onHandshake(ConnectionInfo $connectionInfo): Promise {
     return new Success();
    }
    public function handle(Connection $connection): Promise {
     return new Success();
    }
    public function getSubProtocols() : array{
     return [];
    }
});

$server->run();
```
