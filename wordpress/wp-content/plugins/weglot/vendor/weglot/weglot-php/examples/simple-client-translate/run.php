<?php

require_once __DIR__. '/vendor/autoload.php';

use Weglot\Client\Client;
use Weglot\Client\Endpoint\Translate;
use GuzzleHttp\Exception\GuzzleException;
use Weglot\Client\Api\Exception\MissingRequiredParamException;
use Weglot\Client\Api\Exception\InvalidWordTypeException;
use Weglot\Client\Api\Exception\MissingWordsOutputException;
use Weglot\Client\Api\Exception\InputAndOutputCountMatchException;
use Weglot\Client\Api\TranslateEntry;
use Weglot\Client\Api\Enum\BotType;
use Weglot\Client\Api\WordEntry;
use Weglot\Client\Api\Enum\WordType;

// DotEnv
$dotenv = new \Dotenv\Dotenv(__DIR__);
$dotenv->load();

// TranslateEntry
$params = [
    'language_from' => 'en',
    'language_to' => 'de',
    'title' => 'Weglot | Translate your website - Multilingual for WordPress, Shopify, ...',
    'request_url' => 'https://weglot.com/',
    'bot' => BotType::HUMAN
];
try {
    $translate = new TranslateEntry($params);
    $translate->getInputWords()
        ->addOne(new WordEntry('This is a blue car', WordType::TEXT))
        ->addOne(new WordEntry('This is a black car', WordType::TEXT));
} catch (InvalidWordTypeException $e) {
    // input params issues, WordType on WordEntry construct needs to be valid
    die($e->getMessage());
} catch (MissingRequiredParamException $e) {
    // input params issues, just need to have required fields
    die($e->getMessage());
}

// Client
$client = new Client(getenv('WG_API_KEY'));
$translate = new Translate($translate, $client);

// Run API :)
try {
    $object = $translate->handle();
} catch (InvalidWordTypeException $e) {
    // input params issues, shouldn't happen on server response
    die($e->getMessage());
} catch (MissingRequiredParamException $e) {
    // input params issues, shouldn't happen on server response
    die($e->getMessage());
} catch (MissingWordsOutputException $e) {
    // api return doesn't contains "to_words", shouldn't happen on server response
    die($e->getMessage());
} catch (InputAndOutputCountMatchException $e) {
    // api return doesn't contains same number of input & output words, shouldn't happen on server response
    die($e->getMessage());
} catch (GuzzleException $e) {
    // network issues
    die($e->getMessage());
}

// dumping returned object
var_dump($object);
