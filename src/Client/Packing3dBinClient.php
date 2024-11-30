<?php

declare(strict_types=1);

namespace App\Client;

use App\Enum\Packing3dBinApiLocaleEnum;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class Packing3dBinClient
{
    private Client $client;

    private string $apiUrl = 'https://%s.3dbinpacking.com/packer/';

    private ?string $username;

    private ?string $apiKey;

    /** @throws \Exception */
    public function __construct()
    {
        $this->username = $_ENV['API_USERNAME'] ?? null;
        $this->apiKey = $_ENV['API_KEY'] ?? null;
        $localization = $_ENV['API_LOCALE'] ?? Packing3dBinApiLocaleEnum::GLOBAL_API;

        if ($this->username === null || $this->apiKey === null) {
            throw new \Exception('Environment variables API_USERNAME or API_KEY are missing');
        }

        $this->apiUrl = sprintf($this->apiUrl, $localization);

        $this->client = new Client();
    }

    /** @throws GuzzleException */
    public function packShipment(array $products, array $boxes): ResponseInterface
    {
        $data = [
            'username' => $this->username,
            'api_key' => $this->apiKey,
            'bins' => $boxes,
            'items' => $products,
        ];

        return $this->client->post($this->apiUrl . 'packIntoMany', [
            'json' => $data
        ]);
    }
}
