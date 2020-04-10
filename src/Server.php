<?php

namespace ekstazi\websocket\server\amphp;

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Options;
use Amp\Http\Server\Router;
use Amp\Loop;
use Amp\Promise;
use Amp\Socket\Server as SocketServer;
use Amp\Websocket\Options as WebsocketOptions;
use Amp\Websocket\Server\Websocket;
use ekstazi\websocket\server\Handler as HandlerInterface;
use ekstazi\websocket\server\Server as ServerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function Amp\call;

final class Server implements ServerInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @var HttpServer
     */
    private $server;
    /**
     * @var SocketServer
     */
    private $socket;

    public function __construct(SocketServer $socket, LoggerInterface $logger = null, Options $options = null)
    {
        $logger = $logger ?? new NullLogger();
        $this->router = new Router();
        $this->server = new HttpServer([$socket], $this->router, $logger, $options);
        $this->socket = $socket;
    }

    /**
     * @inheritDoc
     */
    public function addRoute(string $route, HandlerInterface $handler, WebsocketOptions $options = null): void
    {
        $this->router->addRoute('GET', $route, new Websocket(new Handler($handler), $options));
    }

    public function run(): void
    {
        Loop::run(function () {
            Loop::onSignal(SIGINT, [$this, 'stop']);
            Loop::onSignal(SIGTERM, [$this, 'stop']);
            yield $this->server->start();
        });
    }

    public function stop(): Promise
    {
        return call(function () {
            yield $this->server->stop();
            $this->socket->close();
            Loop::stop();
        });
    }
}
