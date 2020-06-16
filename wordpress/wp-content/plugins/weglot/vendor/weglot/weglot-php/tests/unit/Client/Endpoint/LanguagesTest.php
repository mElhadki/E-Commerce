<?php

use Weglot\Client\Client;
use Weglot\Client\Endpoint\Languages;
use Weglot\Client\Api\LanguageCollection;

class LanguagesTest extends \Codeception\Test\Unit
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
     * @var LanguageCollection
     */
    protected $languages;

    /**
     * Init client
     */
    protected function _before()
    {
        $this->client = new Client(getenv('WG_API_KEY'), 3);
        $endpoint = new Languages($this->client);
        $this->languages = $endpoint->handle();
    }

    // tests
    public function testCount()
    {
        $this->assertEquals(108, \count($this->languages));
    }

    public function testGetCode()
    {
        $this->assertEquals('Finnish', $this->languages->getCode('fi')->getEnglishName());
        $this->assertEquals('Hrvatski', $this->languages->getCode('hr')->getLocalName());
        $this->assertNull($this->languages->getCode('foo'));
    }

    public function testSerialize()
    {
        $json = json_encode($this->languages->getCode('fa'));
        $expected = '{"code":"fa","english":"Persian","local":"\u0641\u0627\u0631\u0633\u06cc","rtl":true}';
        $this->assertEquals($expected, $json);

        $json = json_encode($this->languages->getCode('fr'));
        $expected = '{"code":"fr","english":"French","local":"Fran\u00e7ais","rtl":false}';
        $this->assertEquals($expected, $json);

        $json = json_encode($this->languages->getCode('ar'));
        $expected = '{"code":"ar","english":"Arabic","local":"\u0627\u0644\u0639\u0631\u0628\u064a\u0629\u200f","rtl":true}';
        $this->assertEquals($expected, $json);

        $json = json_encode($this->languages->getCode('he'));
        $expected = '{"code":"he","english":"Hebrew","local":"\u05e2\u05d1\u05e8\u05d9\u05ea","rtl":true}';
        $this->assertEquals($expected, $json);

        $json = json_encode($this->languages->getCode('no'));
        $expected = '{"code":"no","english":"Norwegian","local":"Norsk","rtl":false}';
        $this->assertEquals($expected, $json);
    }
}
