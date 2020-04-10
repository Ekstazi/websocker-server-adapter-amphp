<?php

namespace ekstazi\websocket\server\amphp;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Status;
use Amp\Promise;
use Amp\Success;
use Amp\Websocket\Client;
use Amp\Websocket\Code;
use Amp\Websocket\Server\ClientHandler;
use Amp\Websocket\Server\Websocket;
use ekstazi\websocket\common\amphp\Reader;
use ekstazi\websocket\common\amphp\Writer;
use ekstazi\websocket\server\Handler as HandlerInterface;
use function Amp\call;

final class Handler implements ClientHandler
{
    /**
     * @var Handler
     */
    private $handler;

    public function __construct(HandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function onStart(Websocket $endpoint): Promise
    {
        return new Success();
    }

    public function onStop(Websocket $endpoint): Promise
    {
        return new Success();
    }

    public function handleHandshake(Request $request, Response $response): Promise
    {
        return call(function () use ($request, $response) {
            $supportedSubProtocols = $this->handler->getSubProtocols();
            $subProtocols = $request->getHeaderArray('Sec-Websocket-Protocol');
            $subProtocols = \array_filter(\array_map('trim', \explode(',', \implode(',', $subProtocols))));

            if (\count($subProtocols) > 0 || \count($supportedSubProtocols) > 0) {
                $match = \array_intersect($subProtocols, $supportedSubProtocols);

                if (!$match) {
                    $response->setStatus(Status::UPGRADE_REQUIRED, 'No Sec-WebSocket-Protocols requested supported');
                    return $response;
                }
                if ($match) {
                    $response->addHeader('Sec-WebSocket-Protocol', \reset($match));
                }
            }
            $connectionInfo = $this->createConnectionInfo($request);
            try {
                yield $this->handler->onHandshake($connectionInfo);
            } catch (\Throwable $exception) {
                $response->setStatus(Status::UPGRADE_REQUIRED, $exception->getMessage());
                return $response;
            }
            return $response;
        });
    }

    public function handleClient(Client $client, Request $request, Response $response): Promise
    {
        $connectionInfo = $this->createConnectionInfo($request);
        $connection = new Connection(
            new Reader($client),
            new Writer($client),
            $connectionInfo
        );
        return call(function () use ($connection, $client) {
            try {
                return yield $this->handler->handle($connection);
            } catch (\Throwable $throwable) {
                $codes = (new \ReflectionClass(Code::class))->getConstants();
                $code = \in_array($throwable->getCode(), $codes) ? $throwable->getCode() : Code::UNEXPECTED_SERVER_ERROR;
                return $client->close($code, $throwable->getMessage());
            }
        });
    }

    /**
     * @param Request $request
     * @return ConnectionInfo
     */
    private function createConnectionInfo(Request $request): ConnectionInfo
    {
        \parse_str($request->getUri()->getQuery(), $args);
        if ($request->hasAttribute(Router::class)) {
            $args = \array_merge($args, $request->getAttribute(Router::class));
        }

        return new ConnectionInfo($request->getClient(), $args);
    }
}
