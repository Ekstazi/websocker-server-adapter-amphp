<?php

namespace ekstazi\websocket\server\test\amphp;

use Amp\Http\Server\Driver\Client;
use Amp\Socket\SocketAddress;
use ekstazi\websocket\server\amphp\ConnectionInfo;
use ekstazi\websocket\server\ConnectionInfo as ConnectionInfoInterface;
use PHPUnit\Framework\TestCase;

class ConnectionInfoTest extends TestCase
{

    public function testConstruct()
    {
        $client = $this->createStub(Client::class);
        $info = new ConnectionInfo($client);
        self::assertInstanceOf(ConnectionInfoInterface::class, $info);
    }

    public function testGetRemoteAddress()
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('getRemoteAddress')
            ->willReturn(new SocketAddress('127.0.0.1'));

        $info = new ConnectionInfo($client);
        self::assertEquals('127.0.0.1', $info->getRemoteAddress());
    }

    public function testGetId()
    {
        $client = $this->createMock(Client::class);
        $client->expects(self::once())
            ->method('getId')
            ->willReturn(123);

        $info = new ConnectionInfo($client);
        self::assertEquals('123', $info->getId());

    }

    public function testGetArgs()
    {
        $client = $this->createMock(Client::class);
        $args = ['t' => 'ee'];
        $info = new ConnectionInfo($client, $args);
        self::assertEquals($args, $info->getArgs());

    }

}
