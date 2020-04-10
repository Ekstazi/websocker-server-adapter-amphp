<?php

namespace ekstazi\websocket\server\test\amphp;

use Amp\Socket\Server as ServerSocket;
use ekstazi\websocket\server\amphp\ServerFactory;
use ekstazi\websocket\server\Server;
use ekstazi\websocket\server\ServerFactory as ServerFactoryInterface;
use PHPUnit\Framework\TestCase;

class ServerFactoryTest extends TestCase
{

    public function testConstruct()
    {
        $factory = new ServerFactory();
        self::assertInstanceOf(ServerFactoryInterface::class, $factory);
    }

    public function testCreate()
    {
        $factory = new ServerFactory();
        $server = $factory->create(ServerSocket::listen("tcp://127.0.0.1:8000"));
        self::assertInstanceOf(Server::class, $server);
    }
}
