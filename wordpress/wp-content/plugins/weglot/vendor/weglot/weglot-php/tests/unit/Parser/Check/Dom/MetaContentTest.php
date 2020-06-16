<?php

use Weglot\Parser\ConfigProvider\ManualConfigProvider;
use Weglot\Client\Api\Enum\BotType;
use Weglot\Client\Client;
use Weglot\Parser\Parser;

class MetaContentTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var ManualConfigProvider
     */
    protected $config;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Parser
     */
    protected $parser;

    protected $content = '<html><head><title>MyWebPage</title><meta name="description" content="This is my first web page, be kind :)" /><meta</head><body>Coucou</body></html>';

    protected function _before()
    {
        $this->url = 'https://foo.bar/baz';

        // Config manually
        $this->config = new ManualConfigProvider($this->url, BotType::HUMAN);

        // Client
        $this->client = new Client(getenv('WG_API_KEY'), 3);
    }

    // tests
    /*
    public function testCheck()
    {
        // Parser
        $this->parser = new Parser($this->client, $this->config);

        // Run the Parser
        $translatedContent = $this->parser->translate(
            $this->content,
            'en',
            'de'
        );

        $old = $this->_getSimpleDom($this->content);
        $new = $this->_getSimpleDom($translatedContent);

        $oldContent = $old->find('meta[name="description"]', 0)->content;
        $newContent = $new->find('meta[name="description"]', 0)->content;

        $this->assertEquals('This is my first web page, be kind :)', $oldContent);
        $this->assertNotEquals($oldContent, $newContent);
    }*/

    private function _getSimpleDom($source)
    {
        return \WGSimpleHtmlDom\str_get_html(
            $source,
            true,
            true,
            WG_DEFAULT_TARGET_CHARSET,
            false,
            WG_DEFAULT_BR_TEXT,
            WG_DEFAULT_SPAN_TEXT
        );
    }
}
