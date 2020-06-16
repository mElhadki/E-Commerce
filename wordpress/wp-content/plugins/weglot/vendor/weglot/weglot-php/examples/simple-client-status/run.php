<?php

require_once __DIR__. '/vendor/autoload.php';

use Weglot\Client\Client;
use GuzzleHttp\Exception\GuzzleException;
use Weglot\Client\Endpoint\Status;

// DotEnv
$dotenv = new \Dotenv\Dotenv(__DIR__);
$dotenv->load();

// Client
$client = new Client(getenv('WG_API_KEY'));
$status = new Status($client);

// Run API :)
try {
    $object = $status->handle();
} catch (GuzzleException $e) {
    // network issues
    die($e->getMessage());
}

// dumping returned object
var_dump($object);
