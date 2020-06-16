<?php

require_once __DIR__. '/vendor/autoload.php';

use Weglot\Client\Client;
use Weglot\Parser\Parser;
use Weglot\Parser\ConfigProvider\ServerConfigProvider;
use Weglot\Parser\ConfigProvider\ManualConfigProvider;
use Weglot\Client\Api\Enum\BotType;
use Weglot\Util\Site;

// DotEnv
$dotenv = new \Dotenv\Dotenv(__DIR__);
$dotenv->load();

// Url to parse
$url = 'https://weglot.com/documentation/getting-started';

// Config with $_SERVER variables
$_SERVER['SERVER_NAME'] = 'weglot.com';
$_SERVER['REQUEST_URI'] = '/documentation/getting-started';
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PROTOCOL'] = 'http//';
$_SERVER['SERVER_PORT'] = 443;
$_SERVER['HTTP_USER_AGENT'] = 'Google';
$config = new ServerConfigProvider();

// Config manually
$config = new ManualConfigProvider($url, BotType::HUMAN);

// Client
$client = new Client(getenv('WG_API_KEY'));
$parser = new Parser($client, $config);

// Run the Parser
$translatedContent = $parser->translate(Site::get($url), 'en', 'de');

// dumping returned object
echo $translatedContent;
