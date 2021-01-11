<?php

namespace App\Services;

use Psr\Http\Message\ResponseInterface;

class ResponseTransformer
{
    /**
     * @param ResponseInterface $response
     * @return array
     */
    public static function toArray(ResponseInterface $response): array
    {
        $body = (string)$response->getBody();
        $body = json_decode($body, true);

        if (!is_array($body)) {
            return [];
        }

        return $body;
    }
}