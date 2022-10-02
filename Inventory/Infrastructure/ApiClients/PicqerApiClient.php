<?php


namespace App\Inventory\Infrastructure\ApiClients;

use Picqer\Api\Client;

class PicqerApiClient extends ApiClient
{

    function getClient()
    {
        $subDomain = config('picqer.subdomain');
        $apiKey = config('picqer.api_key');
        $apiClient = new Client($subDomain, $apiKey);
        $apiClient->enableRetryOnRateLimitHit();
        $apiClient->setUseragent("Web Services");
        return $apiClient;
    }
}
