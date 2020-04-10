<?php

namespace ekstazi\websocket\server\amphp;

use Amp\Http\Server\Driver\Client;
use ekstazi\websocket\server\ConnectionInfo as ConnectionInfoInterface;

final class ConnectionInfo implements ConnectionInfoInterface
{
    /**
     * @var Client
     */
    private $client;
    /**
     * @var array
     */
    private $args;

    public function __construct(Client $client, array $args = [])
    {
        $this->client = $client;
        $this->args = $args;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function getRemoteAddress(): string
    {
        return $this->client->getRemoteAddress();
    }

    public function getId(): string
    {
        return $this->client->getId();
    }
}
