<?php

namespace EasySwoole\RedisPool;

use EasySwoole\Component\Singleton;
use EasySwoole\Pool\AbstractPool;
use EasySwoole\Redis\ClusterConfig;
use EasySwoole\Redis\Config;
use EasySwoole\Pool\Config as PoolConfig;
use EasySwoole\Redis\Redis;
use EasySwoole\Redis\RedisCluster;

class RedisPool
{
    use Singleton;

    protected array $container = [];

    function register(Config|ClusterConfig $config, string $name ='default'):PoolConfig
    {
        if(isset($this->container[$name])){
            //已经注册，则抛出异常
            throw new \Exception("redis pool:{$name} is already been register");
        }
        $poolConfig = new PoolConfig();
        $poolConfig->setExtraConf($config);
        $pool = new _Pool($poolConfig);
        $this->container[$name] = $pool;
        return $pool->getConfig();
    }

    function getPool(string $name ='default'): ?AbstractPool
    {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        }
        return null;
    }

    static function defer(string $name ='default',$timeout = null):Redis|RedisCluster|null
    {
        $pool = static::getInstance()->getPool($name);
        if($pool){
            return $pool->defer($timeout);
        }else{
            return null;
        }
    }

    static function invoke(callable $call,string $name ='default',float $timeout = null)
    {
        $pool = static::getInstance()->getPool($name);
        if($pool){
            return $pool->invoke($call,$timeout);
        }else{
            return null;
        }
    }
}