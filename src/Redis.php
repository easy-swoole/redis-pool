<?php

namespace EasySwoole\RedisPool;

use EasySwoole\Component\Singleton;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Pool\Config as PoolConfig;

class Redis
{
    use Singleton;
    protected $container = [];

    function register(string $name, RedisConfig $config): PoolConfig
    {
        if(isset($this->container[$name])){
            //已经注册，则抛出异常
            throw new RedisPoolException("redis pool:{$name} is already been register");
        }
        $pool = new RedisPool($config);
        $this->container[$name] = $pool;
        return $pool->getConfig();
    }

    function get(string $name): ?RedisPool
    {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        }
        return null;
    }

    function pool(string $name): ?RedisPool
    {
        return $this->get($name);
    }

    static function defer(string $name,$timeout = null):?\EasySwoole\Redis\Redis
    {
        $pool = static::getInstance()->pool($name);
        if($pool){
            return $pool->defer($timeout);
        }else{
            return null;
        }
    }

    static function invoke(string $name,callable $call,float $timeout = null)
    {
        $pool = static::getInstance()->pool($name);
        if($pool){
            return $pool->invoke($call,$timeout);
        }else{
            return null;
        }
    }
}