<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;

class ResponseHandler
{
    public function createResponse(int $statusCode, array|string $data): ResponseInterface
    {
        return new Response($statusCode, ['Content-Type' => 'application/json'], json_encode($data));
    }
}
