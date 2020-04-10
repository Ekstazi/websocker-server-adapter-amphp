<?php

namespace ekstazi\websocket\server\amphp;

use ekstazi\websocket\common\internal\Connection as BaseConnection;
use ekstazi\websocket\common\Reader;
use ekstazi\websocket\common\Writer;
use ekstazi\websocket\server\Connection as ConnectionInterface;
use ekstazi\websocket\server\ConnectionInfo as ConnectionInfoInterface;
use ekstazi\websocket\server\internal\ConnectionInfoTrait;

class Connection extends BaseConnection implements ConnectionInterface
{
    use ConnectionInfoTrait;

    public function __construct(Reader $reader, Writer $writer, ConnectionInfoInterface $metaInfo)
    {
        parent::__construct($reader, $writer);
        $this->connectionInfo = $metaInfo;
    }
}
