<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2019/10/15 0015
 * Time: 14:46
 */

namespace  EasySwoole\RedisPool;

use EasySwoole\Pool\MagicPool;
use EasySwoole\Redis\Config\RedisClusterConfig;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Redis\Redis;
use EasySwoole\Redis\RedisCluster;

class RedisPool extends MagicPool
{
    function __construct(RedisConfig $redisConfig)
    {
        parent::__construct(function ()use($redisConfig){
            if ($redisConfig instanceof RedisClusterConfig){
                $redis = new RedisCluster($redisConfig);
            }else{
                $redis = new Redis($redisConfig);
            }
            return $redis;
        });
    }
}