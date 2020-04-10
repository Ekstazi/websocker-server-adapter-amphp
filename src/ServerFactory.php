<?php

namespace ekstazi\websocket\server\amphp;

use Amp\Http\Server\Options;
use Amp\Socket\Server as SocketServer;
use ekstazi\websocket\server\Server as ServerInterface;
use ekstazi\websocket\server\ServerFactory as ServerFactoryInterface;
use Psr\Log\LoggerInterface;

class ServerFactory implements ServerFactoryInterface
{
    /**
     * @var Options
     */
    private $defaultOptions;

    public function __construct(Options $defaultOptions = null)
    {
        $this->defaultOptions = $defaultOptions;
    }

    public function create(SocketServer $socket, LoggerInterface $logger = null, Options $options = null): ServerInterface
    {
        $options = $options ?? $this->defaultOptions;
        return new Server($socket, $logger, $options);
    }
}
