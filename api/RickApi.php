<?php
namespace breadhead\rickAnalytics\api;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class RickApi
{
    const BASE_URL = 'https://exchange.rick.ai/';
    const WEBHOOK_PATH = 'webhooks/';
    const TRANSACTION_PATH = 'transactions/';

    private $accountName;

    public function __construct(string $accountName)
    {
        $this->accountName = $accountName;
    }

    public function sendTransaction(string $method, array $fields): bool
    {
        $path = self::TRANSACTION_PATH . $this->accountName . '/' . $method;

        return $this->makeCall($path, $fields);
    }

    public function sendLead(array $fields): bool
    {
        $path = self::WEBHOOK_PATH . $this->accountName . '/crm/lead';

        return$this->makeCall($path, $fields);
    }

    private function makeCall(string $path, array $parameters = []): bool
    {
        $client = new Client(['base_uri' => self::BASE_URL]);

        $response = $client->post($path, [RequestOptions::JSON => $parameters]);

        $responseCode = $response->getStatusCode();

        switch ($responseCode)
        {
            case 200:
                return true;

                break;
            case 400:
                $body = $response->getBody();

                throw new RickApiException($body);

                break;
            case 404:
                throw new RickApiException(sprintf('Unknown path %s', $path));

                break;
            case 500:
                throw new RickApiException('Rick Api does not respond');

                break;
            default:

                throw new RickApiException(sprintf('Unknown response code %s', $responseCode));
        }

        return false;
    }
}
