<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ApiResponseService implements ApiResponseServiceInterface
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param $url
     * @return mixed|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function apiGet($url)
    {
        try {
            $response = $this->client->get($url);
            return json_decode($response->getBody(), true);
        } catch (GuzzleException $g) {
            return $g->getMessage();
        }
    }
}