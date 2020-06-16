<?php

require_once __DIR__. '/vendor/autoload.php';

use Weglot\Client\Client;
use Weglot\Client\Endpoint\Languages;
use Weglot\Client\Api\Exception\InvalidLanguageException;

// DotEnv
$dotenv = new \Dotenv\Dotenv(__DIR__);
$dotenv->load();

// Client
$client = new Client(getenv('WG_API_KEY'));
$languages = new Languages($client);

// Run API :)
$object = $languages->handle();

// dumping returned object
var_dump($object->getCode('fi'));
