<?php

namespace ekstazi\websocket\server\amphp;

use ekstazi\websocket\server\ServerFactory as ServerFactoryInterface;
use Psr\Container\ContainerInterface;

final class FactoryBuilder
{
    public function __invoke(ContainerInterface $container): ServerFactoryInterface
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = $config['websocket'] ?? [];
        $options = $config['serverOptions'] ?? null;

        return new ServerFactory($options);
    }
}
