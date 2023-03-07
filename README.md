# Redis-Pool
Redis-Pool 基于 [pool连接池管理](https://github.com/easy-swoole/pool),[redis协程客户端](https://github.com/easy-swoole/redis) 封装的组件


## 安装
```shell
composer require easyswoole/redis-pool
```


## 连接池注册
使用连接之前注册redis连接池:

```php
//redis连接池注册(config默认为127.0.0.1,端口6379)
\EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisConfig(),'redis');

//redis集群连接池注册
\EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisClusterConfig([
        ['172.16.253.156', 9001],
        ['172.16.253.156', 9002],
        ['172.16.253.156', 9003],
        ['172.16.253.156', 9004],
    ]
),'redisCluster');
```

## 连接池配置
当注册好时,将返回连接池的poolConfig用于配置连接池


## 使用连接池:

```php

    $config = new Config(
        [
            'host'=>"",
            'port'=>"6300",
            'auth'=>"",
            "db"=>0
        ]
    );

    RedisPool::getInstance()->register($config);

    $client = RedisPool::defer();
    $ret = $client->get("a");
    var_dump($ret);
    $client->set("a",time());
    $ret = $client->get("a");
    var_dump($ret);
    
    RedisPool::invoke(function (Redis $redis){
        var_dump($redis->get("a"));
    });

```
！！！注意，在未指定连接池名称是，注册的连接池名称为默认的```default```
