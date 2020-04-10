<?php

namespace ekstazi\websocket\server\test\amphp;

use ekstazi\websocket\common\Reader;
use ekstazi\websocket\common\Writer;
use ekstazi\websocket\server\amphp\Connection;
use ekstazi\websocket\server\Connection as ConnectionInterface;
use ekstazi\websocket\server\ConnectionInfo;
use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{

    public function testConstruct()
    {
        $reader = $this->createStub(Reader::class);
        $writer = $this->createStub(Writer::class);
        $info = $this->createStub(ConnectionInfo::class);
        $connection = new Connection($reader, $writer, $info);
        self::assertInstanceOf(ConnectionInterface::class, $connection);
    }
}
