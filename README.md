# Redis Pool

基于 [EasySwoole Pool](https://github.com/easy-swoole/pool) 和 [phpredis](https://github.com/phpredis/phpredis) 的 Redis 协程连接池，支持单机与 Redis Cluster。

## 安装

```shell
composer require easyswoole/redis-pool
```

需要 PHP 8.1+、Swoole 5.1+ 和 phpredis 扩展。

## 单机模式

```php
use EasySwoole\RedisPool\Config;
use EasySwoole\RedisPool\RedisPool;

$config = new Config([
    'host' => '127.0.0.1',
    'port' => 6379,
    'password' => null,
    'database' => 0,
    'maxObjectNum' => 20,
    'minObjectNum' => 2,
]);

RedisPool::getInstance()->register($config);
```

Redis ACL 认证可同时设置 `username` 和 `password`。

## Cluster 模式

```php
$config = new Config([
    'mode' => Config::MODE_CLUSTER,
    'seeds' => [
        '127.0.0.1:7001',
        '127.0.0.1:7002',
        '127.0.0.1:7003',
    ],
    'password' => 'secret',
]);

RedisPool::getInstance()->register($config, 'cluster');
```

Redis Cluster 只支持数据库 0。

## 使用

`defer()` 会在当前协程结束时自动归还连接：

```php
$redis = RedisPool::defer();
$redis->set('key', 'value');
$value = $redis->get('key');
```

`invoke()` 会在回调结束后立即归还连接：

```php
$value = RedisPool::invoke(
    static fn (Redis $redis) => $redis->get('key')
);

$clusterValue = RedisPool::invoke(
    static fn (RedisCluster $redis) => $redis->get('key'),
    'cluster'
);
```

未指定名称时，连接池名称默认为 `default`。注册时返回的 `Config` 可用于访问连接池配置。

## 测试

集成测试从环境变量读取 Redis 配置，不在代码中保存密码：

```shell
REDIS_HOST=127.0.0.1 \
REDIS_PORT=6379 \
REDIS_PASSWORD='your-password' \
composer test
```

可选环境变量：`REDIS_USERNAME`、`REDIS_DATABASE`、`REDIS_CONNECT_TIMEOUT`、`REDIS_READ_TIMEOUT` 和 `REDIS_POOL_MAX_OBJECTS`。未提供 `REDIS_HOST` 或 `REDIS_PORT` 时，集成测试会自动跳过，配置单元测试仍会运行。

## 性能测试

2026-10-11 在 Redis 同机环境中实测，通过 `127.0.0.1:6379` 连接以排除公网延迟。测试机为 2 vCPU，运行 PHP 8.4.26、Swoole 6.2.2、phpredis 6.3.0 和 Redis 6.2.22。每组预热 1 秒，连续测量 8 秒；每次业务循环执行一次 `SETEX` 和一次 `GET` 并校验返回值。

| 用法 | 并发数 | 业务循环/秒 | 估算 Redis 命令/秒 | P50 延迟 | P95 延迟 | P99 延迟 | 错误 |
| --- | ---: | ---: | ---: | ---: | ---: | ---: | ---: |
| `invoke()` | 1 | 6,610 | 19,829 | 0.144 ms | 0.185 ms | 0.246 ms | 0 |
| `invoke()` | 16 | 13,415 | 40,245 | 1.167 ms | 1.386 ms | 1.863 ms | 0 |
| `invoke()` | 64 | 11,109 | 33,328 | 5.765 ms | 6.066 ms | 6.195 ms | 0 |
| `defer()` | 64 | 20,831 | 41,662 | 3.021 ms | 3.308 ms | 3.634 ms | 0 |

`invoke()` 每次借出连接时会调用 `beforeUse()` 执行健康检查，因此每个业务循环实际包含 `PING + SETEX + GET` 三条 Redis 命令。`defer()` 在同一协程内复用已借出的连接，每个循环为两条命令，因而更适合高频连续操作。并发从 16 增加到 64 时 `invoke()` 吞吐量下降，表明该 2 vCPU 环境下频繁健康检查与协程调度已成为主要开销。

压测期间应用层错误、Redis `rejected_connections` 和 `evicted_keys` 均为 0，Redis 服务无 warning 或更高级别日志。以上数据是特定机器与配置下的参考值，不代表所有生产环境。
