<?php

namespace EasySwoole\RedisPool\Client;

class RedisCluster extends \EasySwoole\Redis\RedisCluster
{
    public $__lastPingTime = 0;
}