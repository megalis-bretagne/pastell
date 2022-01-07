<?php

namespace Pastell\Service;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;

class LoginAttemptLimit
{
    private const RATE_LIMIT_REDIS_NAMESPACE =  'rate-limit-login';
    private const RATE_LIMIT_INFO_TTL_IN_SECONDS = 3600;
    private const RATE_LIMIT_LOGIN_ATTEMPT_PER_MINUTE = 5;

    private $redis_server;
    private $redis_port;

    public function __construct(string $redis_server, int $redis_port)
    {
        $this->redis_server = $redis_server;
        $this->redis_port = $redis_port;
    }

    public function isLoginAttemptAuthorized(string $login): bool
    {
        $loginAttemptFactory = $this->getLimiterFactory();
        $limiter = $loginAttemptFactory->create($login);
        return  $limiter->consume()->isAccepted();
    }

    public function resetLoginAttempt(string $login)
    {
        $loginAttemptFactory = $this->getLimiterFactory();
        $limiter = $loginAttemptFactory->create($login);
        $limiter->reset();
    }

    public function getRateLimit(string $login): RateLimit
    {
        $loginAttemptFactory = $this->getLimiterFactory();
        $limiter = $loginAttemptFactory->create($login);
        return $limiter->consume(0);
    }



    /**
     * Enlever tout ce bordel une fois qu'on aura intégré l'injecteur de dépendance de Symfony.
     */
    public function getLimiterFactory(): RateLimiterFactory
    {
        $connection = RedisAdapter::createConnection(
            sprintf("redis://%s:%d", $this->redis_server, $this->redis_port)
        );
        $cache = new RedisAdapter(
            $connection,
            self::RATE_LIMIT_REDIS_NAMESPACE,
            self::RATE_LIMIT_INFO_TTL_IN_SECONDS
        );

        $cacheStorage = new CacheStorage($cache);

        return new RateLimiterFactory(
            [
                'id' => 'login',
                'policy' => 'fixed_window',
                'limit' => self::RATE_LIMIT_LOGIN_ATTEMPT_PER_MINUTE,
                'interval' => '1 minute'
            ],
            $cacheStorage
        );
    }
}
