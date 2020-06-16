<?php

use Weglot\Client\Client;
use Weglot\Client\Endpoint\Translate;
use Weglot\Client\Api\TranslateEntry;
use Weglot\Client\Api\Enum\BotType;
use Weglot\Client\Api\WordEntry;
use Weglot\Client\Api\Enum\WordType;
use Weglot\Client\Api\WordCollection;
use Predis\Client as Redis;
use Cache\Adapter\Predis\PredisCachePool;

class CachedTranslateTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * @var TranslateEntry
     */
    protected $entry;

    /**
     * @var Translate
     */
    protected $translate;

    /**
     * Init client
     */
    protected function _before()
    {
        // Client
        $this->client = new Client(getenv('WG_API_KEY'), 3);

        // Redis
        $this->redis = new Redis([
            'scheme' => getenv('REDIS_SCHEME'),
            'host'   => getenv('REDIS_HOST'),
            'port'   => getenv('REDIS_PORT'),
        ]);
        $this->redis->connect();

        // PSR-6 CacheItemPool
        $itemPool = new PredisCachePool($this->redis);
        $itemPool->clear();
        $this->client->setCacheItemPool($itemPool);

        // TranslateEntry
        $params = [
            'language_from' => 'en',
            'language_to' => 'de',
            'title' => 'Weglot | Translate your website - Multilingual for WordPress, Shopify, ...',
            'request_url' => 'https://weglot.com/',
            'bot' => BotType::HUMAN
        ];

        $this->entry = new TranslateEntry($params);
        $this->entry->getInputWords()
                ->addOne(new WordEntry('This is a blue car', WordType::TEXT))
                ->addOne(new WordEntry('This is a black car', WordType::TEXT));

        // Translate endpoint
        $this->translate = new Translate($this->entry, $this->client);
    }

    // tests
    public function testSetOutputWords()
    {
        $this->entry->setOutputWords(null);
        $this->assertTrue($this->entry->getOutputWords() instanceof WordCollection);
        $this->assertEquals(0, $this->entry->getOutputWords()->count());
    }

    public function testGetParams()
    {
        $params = $this->entry->getParams();
        $this->assertEquals('en', $params['language_from']);
        $this->assertEquals('de', $params['language_to']);
        $this->assertEquals('https://weglot.com/', $params['request_url']);
        $this->assertEquals(BotType::HUMAN, $params['bot']);
    }

    public function testEndpointCountWord()
    {
        $translated = $this->translate->handle();

        $this->assertEquals($this->entry->getInputWords()->count(), $translated->getOutputWords()->count());
    }

    public function testTranslateEntry()
    {
        $this->assertTrue($this->translate->getTranslateEntry() instanceof TranslateEntry);
        $this->assertTrue($this->translate->getTranslateEntry() === $this->entry);
    }

    public function testPath()
    {
        $this->assertEquals('/translate', $this->translate->getPath());
    }

    // public function testCachedRequest()
    // {
        // $translated = $this->translate->handle();
        // $this->assertEquals($this->entry->getInputWords()->count(), $translated->getOutputWords()->count());

        // $translated = $this->translate->handle();
        // $this->assertEquals($this->entry->getInputWords()->count(), $translated->getOutputWords()->count());
    // }
}
