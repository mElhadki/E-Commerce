<?php

use Weglot\Util\JsonUtil;
use Weglot\Client\Api\WordCollection;
use Weglot\Client\Api\WordEntry;

class JsonUtilTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var array
     */
    protected $json = [];

    protected function _before()
    {
        $raw = <<<EOT
{
  "@context": {
    "name": "http://xmlns.com/foaf/0.1/name",
    "homepage": {
      "@id": "http://xmlns.com/foaf/0.1/workplaceHomepage",
      "@type": "@id"
    },
    "Person": "http://xmlns.com/foaf/0.1/Person"
  },
  "@id": "http://me.example.com",
  "@type": "Person",
  "name": "John Smith",
  "homepage": "http://www.example.com/"
}
EOT;
        $this->json = json_decode($raw, true);
    }


    // tests
    public function testGet()
    {
        $this->assertNull(JsonUtil::get($this->json, 'description'));
        $this->assertEquals('John Smith', JsonUtil::get($this->json, 'name'));
    }

    public function testAdd()
    {
        $words = new WordCollection();
        $words->addOne(new WordEntry('Une voiture bleue'));

        $this->assertEquals(1, $words->count());

        $value = JsonUtil::get($this->json, 'name');
        JsonUtil::add($words, $value);

        $this->assertEquals(2, $words->count());
        $this->assertEquals(new WordEntry($value), $words[1]);
    }

    public function testSet()
    {
        $nextJson = 0;
        $words = new WordCollection();
        $words->addOne(new WordEntry('Une voiture bleue'));

        $this->assertEquals(0, $nextJson);
        $this->assertEquals(1, $words->count());

        $data = JsonUtil::set($words, $this->json, 'name', $nextJson);

        $this->assertEquals(1, $nextJson);
        $this->assertEquals($data['name'], $words[0]->getWord());
    }
}
