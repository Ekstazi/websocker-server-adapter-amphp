<?php

namespace ekstazi\websocket\server\test\amphp;

use Amp\Http\Server\Options;
use ekstazi\websocket\server\amphp\FactoryBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class FactoryBuilderTest extends TestCase
{

    public function testInvoke()
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects(self::atLeastOnce())
            ->method('has')
            ->willReturn(true);

        $container
            ->expects(self::atLeastOnce())
            ->method('get')
            ->with('config')
            ->willReturn([
                "websocket" => [
                    'serverOptions' => new Options(),
                ]
            ]);

        $factory = new FactoryBuilder();
        $factory->__invoke($container);
    }
}
