<?php

namespace EasySwoole\RedisPool;

use EasySwoole\Pool\Exception\Exception;

/** Redis 连接与连接池配置。 */
class Config extends \EasySwoole\Pool\Config
{

    public const MODE_STANDALONE = 'standalone';
    public const MODE_CLUSTER = 'cluster';

    /** Redis 连接池默认最大连接数。 */
    protected int $maxObjectNum = 20;

    /** 共同配置：连接模式，支持 standalone 和 cluster。 */
    protected string $mode = self::MODE_STANDALONE;

    /** 单机配置：Redis 服务地址。 */
    protected string $host = '127.0.0.1';

    /** 单机配置：Redis 服务端口。 */
    protected int $port = 6379;

    /** Cluster 配置：集群种子节点，格式为 ['127.0.0.1:7001', ...]。 */
    protected array $seeds = [];

    /** 共同配置：Redis ACL 用户名；未启用 ACL 时保持 null。 */
    protected ?string $username = null;

    /** 共同配置：Redis 密码；无密码时保持 null。 */
    protected ?string $password = null;

    /** 单机配置：数据库编号；Cluster 仅支持数据库 0。 */
    protected int $database = 0;

    /** 共同配置：建立连接的超时时间，单位为秒。 */
    protected float $connectTimeout = 3.0;

    /** 共同配置：读取数据的超时时间，单位为秒。 */
    protected float $readTimeout = 3.0;

    /** 单机配置：连接失败后的重试间隔，单位为毫秒。 */
    protected int $retryInterval = 0;

    /** 共同配置：是否使用持久连接。 */
    protected bool $persistent = false;

    /** 共同配置：持久连接标识；Cluster 中作为集群名称使用。 */
    protected ?string $persistentId = null;

    /** 共同配置：phpredis 连接上下文。 */
    protected array $context = [];

    public function getMode(): string { return $this->mode; }

    public function setMode(string $mode): self
    {
        if (!in_array($mode, [self::MODE_STANDALONE, self::MODE_CLUSTER], true)) {
            throw new Exception('redis mode must be standalone or cluster');
        }
        if ($mode === self::MODE_CLUSTER && $this->database !== 0) {
            throw new Exception('redis cluster only supports database 0');
        }
        $this->mode = $mode;
        return $this;
    }

    public function getHost(): string { return $this->host; }

    public function setHost(string $host): self
    {
        if (trim($host) === '') {
            throw new Exception('redis host cannot be empty');
        }
        $this->host = $host;
        return $this;
    }

    public function getPort(): int { return $this->port; }

    public function setPort(int $port): self
    {
        if ($port < 1 || $port > 65535) {
            throw new Exception('redis port must be between 1 and 65535');
        }
        $this->port = $port;
        return $this;
    }

    public function getSeeds(): array { return $this->seeds; }

    public function setSeeds(array $seeds): self
    {
        foreach ($seeds as $seed) {
            if (!is_string($seed) || trim($seed) === '') {
                throw new Exception('redis cluster seeds must be non-empty host:port strings');
            }
        }
        $this->seeds = array_values($seeds);
        return $this;
    }

    public function getUsername(): ?string { return $this->username; }

    public function setUsername(?string $username): self
    {
        if ($username === '') {
            throw new Exception('redis username cannot be empty');
        }
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string { return $this->password; }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getAuth(): string|array|null
    {
        if ($this->username !== null) {
            return [$this->username, $this->password ?? ''];
        }
        return $this->password;
    }

    public function getDatabase(): int { return $this->database; }

    public function setDatabase(int $database): self
    {
        if ($database < 0) {
            throw new Exception('redis database must not be negative');
        }
        if ($this->mode === self::MODE_CLUSTER && $database !== 0) {
            throw new Exception('redis cluster only supports database 0');
        }
        $this->database = $database;
        return $this;
    }

    public function getConnectTimeout(): float { return $this->connectTimeout; }

    public function setConnectTimeout(float $connectTimeout): self
    {
        if ($connectTimeout < 0) {
            throw new Exception('redis connect timeout must not be negative');
        }
        $this->connectTimeout = $connectTimeout;
        return $this;
    }

    public function getReadTimeout(): float { return $this->readTimeout; }

    public function setReadTimeout(float $readTimeout): self
    {
        if ($readTimeout < 0) {
            throw new Exception('redis read timeout must not be negative');
        }
        $this->readTimeout = $readTimeout;
        return $this;
    }

    public function getRetryInterval(): int { return $this->retryInterval; }

    public function setRetryInterval(int $retryInterval): self
    {
        if ($retryInterval < 0) {
            throw new Exception('redis retry interval must not be negative');
        }
        $this->retryInterval = $retryInterval;
        return $this;
    }

    public function isPersistent(): bool { return $this->persistent; }

    public function setPersistent(bool $persistent): self
    {
        $this->persistent = $persistent;
        return $this;
    }

    public function getPersistentId(): ?string { return $this->persistentId; }

    public function setPersistentId(?string $persistentId): self
    {
        if ($persistentId === '') {
            throw new Exception('redis persistent id cannot be empty');
        }
        $this->persistentId = $persistentId;
        return $this;
    }

    public function getContext(): array { return $this->context; }

    public function setContext(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    public function validateConnection(): void
    {
        if ($this->mode === self::MODE_CLUSTER && $this->seeds === []) {
            throw new Exception('redis cluster seeds cannot be empty');
        }
        if ($this->mode === self::MODE_CLUSTER && $this->database !== 0) {
            throw new Exception('redis cluster only supports database 0');
        }
    }

    protected function initialize(): void
    {
        parent::initialize();
        $this->setMode($this->mode);
        $this->setHost($this->host);
        $this->setPort($this->port);
        $this->setSeeds($this->seeds);
        $this->setUsername($this->username);
        $this->setDatabase($this->database);
        $this->setConnectTimeout($this->connectTimeout);
        $this->setReadTimeout($this->readTimeout);
        $this->setRetryInterval($this->retryInterval);
        $this->setPersistentId($this->persistentId);

        $this->validateConnection();
    }
}
