<?php

use Weglot\Client\Client;
use Weglot\Client\Endpoint\Status;

class StatusTest extends \Codeception\Test\Unit
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
     * @var Status
     */
    protected $status;

    /**
     * Init client
     */
    protected function _before()
    {
        $this->client = new Client(getenv('WG_API_KEY'), 3);
        $this->status = new Status($this->client);
    }

    // tests
    public function testEndpoint()
    {
        $this->assertTrue($this->status->handle(), 'API not reachable');
    }

    public function testPath()
    {
        $this->assertEquals('/status', $this->status->getPath());
    }
}
