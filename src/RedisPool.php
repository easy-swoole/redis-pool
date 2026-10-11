<?php

namespace EasySwoole\RedisPool;

use EasySwoole\Component\Singleton;
use EasySwoole\Pool\AbstractPool;
use InvalidArgumentException;

class RedisPool
{
    use Singleton;

    protected array $container = [];

    public function register(Config $config, string $name = 'default'): Config
    {
        if ($name === '') {
            throw new InvalidArgumentException('redis pool name cannot be empty');
        }
        if (isset($this->container[$name])) {
            throw new InvalidArgumentException("redis pool '{$name}' is already registered");
        }

        $pool = new Pool($config);
        $this->container[$name] = $pool;
        return $pool->getConfig();
    }

    public function getPool(string $name = 'default'): ?AbstractPool
    {
        return $this->container[$name] ?? null;
    }

    public static function defer(string $name = 'default', ?float $timeout = null): Client|ClusterClient|null
    {
        $pool = static::getInstance()->getPool($name);
        /** @var Client|ClusterClient|null */
        return $pool?->defer($timeout);
    }

    public static function invoke(callable $call, string $name = 'default', ?float $timeout = null): mixed
    {
        $pool = static::getInstance()->getPool($name);
        return $pool?->invoke($call, $timeout);
    }
}
