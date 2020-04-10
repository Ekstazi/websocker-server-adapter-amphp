<?php

namespace ekstazi\websocket\server\test\amphp;

use Amp\ByteStream\ClosedException;
use Amp\ByteStream\Payload;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Loop;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Promise;
use Amp\Socket\Server as ServerSocket;
use Amp\Success;
use Amp\Websocket\Client;
use Amp\Websocket\Client\Handshake;
use Amp\Websocket\Code;
use ekstazi\websocket\server\amphp\Server;
use ekstazi\websocket\server\Connection;
use ekstazi\websocket\server\ConnectionInfo;
use ekstazi\websocket\server\Handler;
use ekstazi\websocket\server\Server as ServerInterface;
use function Amp\call;
use function Amp\Websocket\Client\connect;

class ServerTest extends AsyncTestCase
{

    public function testConstruct()
    {
        $server = new Server(ServerSocket::listen("tcp://127.0.0.1:8001"));
        self::assertInstanceOf(ServerInterface::class, $server);
    }

    public function testRun()
    {
        $server = $this->createServer(new class {
            public function getSubProtocols()
            {
                return ['binary'];
            }
        });
        Loop::defer(function () use ($server) {
            $handshake = new Handshake('ws://127.0.0.1:8003/ws', null, ['Sec-Websocket-Protocol' => 'binary']);
            /** @var Client $connection */
            $connection = yield connect($handshake);
            yield $connection->sendBinary('test');
            /** @var Payload $frame */
            $frame = yield $connection->receive();
            $data = yield $frame->buffer();
            self::assertEquals('test', $data);
            yield $server->stop();
        });
        $server->run();
    }

    public function testSignals()
    {
        $server = $this->createServer();
        Loop::delay(100, function () {
            $handler = pcntl_signal_get_handler(SIGINT);
            self::assertIsCallable($handler);
            call_user_func($handler, SIGINT);
        });
        $server->run();
    }

    public function testSubProtocolError()
    {
        $server = $this->createServer(new class() {
            public function getSubProtocols()
            {
                return ["binary"];
            }
        });
        Loop::defer(function () use ($server) {
            $handshake = new Handshake('ws://127.0.0.1:8003/ws');
            try {
                /** @var Client $connection */
                yield connect($handshake);
            } catch (Client\ConnectionException $exception) {
                self::assertEquals(426, $exception->getResponse()->getStatus());
                self::assertEquals('No Sec-WebSocket-Protocols requested supported', $exception->getResponse()->getReason());
            }
            yield $server->stop();
        });
        $server->run();
    }

    public function testOnHandshakeError()
    {
        $server = $this->createServer(new class() {
            public function onHandshake()
            {
                throw new \Exception('test');
            }
        });
        Loop::defer(function () use ($server) {
            $handshake = new Handshake('ws://127.0.0.1:8003/ws', );
            try {
                /** @var Client $connection */
                $connection = yield connect($handshake);
            } catch (Client\ConnectionException $exception) {
                self::assertEquals(426, $exception->getResponse()->getStatus());
                self::assertEquals('test', $exception->getResponse()->getReason());
            }
            yield $server->stop();
        });
        $server->run();
    }

    public function testOnHandleError()
    {
        $server = $this->createServer(new class() {
            public function handle()
            {
                throw new \Exception('test');
            }
        });
        Loop::defer(function () use ($server) {
            $handshake = new Handshake('ws://127.0.0.1:8003/ws');
            try {
                /** @var Client $connection */
                $connection = yield connect($handshake);
                $data = yield $connection->send('test');
                self::assertEquals(Code::UNEXPECTED_SERVER_ERROR, $connection->getCloseCode());
            } catch (ClosedException $exception) {
                self::assertEquals(Code::UNEXPECTED_SERVER_ERROR, $exception->getCode());
            }
            yield $server->stop();
        });
        $server->run();
    }

    public function testNotFound()
    {
        $server = $this->createServer();
        Loop::defer(function () use ($server) {
            $client = HttpClientBuilder::buildDefault();
            /** @var Response $response */
            $response = yield $client->request(new Request('http://127.0.0.1:8003/wstest'));
            self::assertEquals(404, $response->getStatus());
            yield $server->stop();
        });
        $server->run();
    }

    /**
     * @return Server
     * @throws
     */
    private function createServer($handler = null): Server
    {
        $handler = new class($handler) implements Handler {
            private $original;

            public function __construct($original)
            {
                $this->original = $original ?? new \stdClass();
            }

            public function onHandshake(ConnectionInfo $connectionInfo): Promise
            {
                return method_exists($this->original, 'onHandshake')
                    ? call_user_func([$this->original, 'onHandshake'], $connectionInfo)
                    : new Success();
            }

            public function handle(Connection $connection): Promise
            {
                if (method_exists($this->original, 'handle')) {
                    return call_user_func([$this->original, 'handle'], $connection);
                }

                return call(function () use ($connection) {
                    $data = yield $connection->read();
                    yield $connection->write($data);
                });
            }

            public function getSubProtocols(): array
            {
                return method_exists($this->original, 'getSubProtocols')
                    ? call_user_func([$this->original, 'getSubProtocols'])
                    : [];
            }
        };
        $socket = ServerSocket::listen("tcp://127.0.0.1:8003");
        $server = new Server($socket);
        $server->addRoute('/ws', $handler);
        return $server;
    }

}
