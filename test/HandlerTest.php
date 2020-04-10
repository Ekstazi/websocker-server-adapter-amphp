<?php

namespace ekstazi\websocket\server\test\amphp;

use Amp\Http\Server\Driver\Client as ServerClient;
use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Amp\Websocket\Client;
use Amp\Websocket\Server\Websocket;
use ekstazi\websocket\server\amphp\Handler;
use ekstazi\websocket\server\Connection;
use ekstazi\websocket\server\ConnectionInfo as ConnectionInfoInterface;
use ekstazi\websocket\server\Handler as HandlerInterface;
use League\Uri\Http;

class HandlerTest extends AsyncTestCase
{

    private function createRequest(): Request
    {
        $request =  new Request(
            $this->createStub(ServerClient::class),
            'GET',
            Http::createFromString('http://127.0.0.1/?t=ee')
        );
        $request->setAttribute(Router::class, ['ee' => 'aa']);
        return $request;
    }

    public function testHandleHandshake()
    {
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects(self::once())
            ->method('onHandshake')
            ->willReturnCallback(function (ConnectionInfoInterface $connectionInfp) {
                return new Success();
            });
        $request = $this->createRequest();
        $response = new Response();
        $clientHandler = new Handler($handler);
        $client = $this->createStub(Client::class);

        $resp = yield $clientHandler->handleHandshake($request, $response);
        self::assertEquals($resp, $response);
    }

    public function testOnStop()
    {
        $handler = $this->createStub(HandlerInterface::class);
        $clientHandler = new Handler($handler);
        $websocket = new Websocket($clientHandler);

        $promise = $clientHandler->onStop($websocket);
        self::assertInstanceOf(Success::class, $promise);
    }

    public function testHandleClient()
    {
        $handler = $this->createMock(HandlerInterface::class);
        $handler->expects(self::once())
            ->method('handle')
            ->willReturnCallback(function (Connection $connection) {
                self::assertEquals(['t' => 'ee', 'ee' => 'aa'], $connection->getArgs());
                return new Success();
            });
        $request = $this->createRequest();
        $response = new Response();
        $clientHandler = new Handler($handler);
        $client = $this->createStub(Client::class);

        $clientHandler->handleClient($client, $request, $response);
    }

    public function testOnStart()
    {
        $handler = $this->createStub(HandlerInterface::class);
        $clientHandler = new Handler($handler);
        $websocket = new Websocket($clientHandler);

        $promise = $clientHandler->onStart($websocket);
        self::assertInstanceOf(Success::class, $promise);
    }
}
