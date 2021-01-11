<?php

namespace App\Wrappers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class GuzzleClientWrapper
{
    /** @var Client */
    private $guzzleClient;

    public function __construct()
    {
        $this->guzzleClient = new Client();
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function request(string $method, string  $uri = '', array $options = []): ResponseInterface
    {
        return $this->guzzleClient->request($method, $uri, $options);
    }

}