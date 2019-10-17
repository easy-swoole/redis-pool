<?php
/**
 * Created by PhpStorm.
 * User: Tioncico
 * Date: 2019/10/15 0015
 * Time: 14:46
 */

namespace  EasySwoole\RedisPool;

use EasySwoole\Pool\Config;
use EasySwoole\Pool\AbstractPool;
use EasySwoole\Redis\Config\RedisClusterConfig;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Redis\Redis;
use EasySwoole\Redis\RedisCluster;

class RedisPool extends AbstractPool
{
    protected $redisConfig;

    /**
     * 重写构造函数,为了传入redis配置
     * RedisPool constructor.
     * @param Config      $conf
     * @param RedisConfig $redisConfig
     * @throws \EasySwoole\Pool\Exception\Exception
     */
    public function __construct(Config $conf,RedisConfig $redisConfig)
    {
        parent::__construct($conf);
        $this->redisConfig = $redisConfig;
    }

    protected function createObject()
    {
        //根据传入的redis配置进行判断是否为集群redis
        if ($this->redisConfig instanceof RedisClusterConfig){
            $redis = new RedisCluster($this->redisConfig);
        }else{
            $redis = new Redis($this->redisConfig);
        }

        return $redis;
    }
}