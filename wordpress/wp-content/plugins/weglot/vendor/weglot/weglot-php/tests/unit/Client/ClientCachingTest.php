<?php

use Weglot\Client\Client;
use Predis\Client as Redis;
use Cache\Adapter\Predis\PredisCachePool;

class ClientCachingTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \Weglot\Client\Client
     */
    protected $client;

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * Init client & redis-server
     */
    protected function _before()
    {
        $this->client = new Client(getenv('WG_API_KEY'), 3);

        $this->redis = new Redis([
            'scheme' => getenv('REDIS_SCHEME'),
            'host'   => getenv('REDIS_HOST'),
            'port'   => getenv('REDIS_PORT'),
        ]);
        $this->redis->connect();

        $itemPool = new PredisCachePool($this->redis);
        $itemPool->clear();
        $this->client->setCacheItemPool($itemPool);
    }

    // tests
    public function testRedisConnection()
    {
        $this->assertTrue($this->redis->isConnected());
    }

    public function testItemPool()
    {
        $this->assertTrue($this->client->getCache()->getItemPool() instanceof PredisCachePool);
    }

    public function testExpire()
    {
        $this->assertEquals(604800, $this->client->getCache()->getExpire());

        $this->client->getCache()->setExpire(240);
        $this->assertEquals(240, $this->client->getCache()->getExpire());
    }

    public function testGenerateKey()
    {
        $cacheKey = $this->client->getCache()->generateKey([
            'method' => 'GET',
            'endpoint' => '/translate',
            'content' => []
        ]);
        $this->assertEquals('wg_8bdaed005c88bda03e938c3de08da157ecbe5dfa', $cacheKey);
    }

    public function testGetItem()
    {
        $key = 'getItem';
        $item = $this->client->getCache()->get($key);

        $this->assertNull($item->get());

        $item->set('some value');
        $this->client->getCache()->save($item);

        $this->assertEquals('some value', $this->client->getCache()->get($key)->get());
    }
}
