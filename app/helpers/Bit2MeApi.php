<?php

namespace App\Helpers;

use GuzzleHttp\Client;

class Bit2MeApi
{
    private $baseUri;

    public function __construct()
    {
        $this->baseUri = env('BIT2ME_BASE_URI');
    }

    public function get($endpoint, $params = [], $subaccountId = null)
    {
        $headers = $this->getAuthHeaders($endpoint);
        if ($subaccountId) {
            $headers['X-SUBACCOUNT-ID'] = $subaccountId;
        }

        $client = new Client();
        $url = $this->baseUri . $endpoint;
        $response = $client->get($url, [
            'headers' => $headers,
            'query' => $params,
        ]);

        return json_decode($response->getBody(), true);
    }

    private function getAuthHeaders($endpoint, $body = null)
    {
        $nonce = time(); 
        $apiKey = env('API_KEY');
        $apiSecret = env('API_SECRET_KEY');
        $messageToSign = $this->getClientMessageToSign($nonce, $endpoint, $body);
        $signature = $this->getMessageSignature($messageToSign, $apiSecret);

        return [
            'x-api-key' => $apiKey,
            'api-signature' => $signature,
            'x-nonce' => $nonce,
        ];
    }

    private function getClientMessageToSign($nonce, $url, $body)
    {
        $hasBody = !empty($body);
        return $hasBody
            ? sprintf('%s:%s:%s', $nonce, $url, json_encode($body))
            : sprintf('%s:%s', $nonce, $url);
    }

    private function getMessageSignature($message, $apiSecret)
    {
        $hash = hash_hmac('sha512', $message, $apiSecret, true);
        return base64_encode($hash);
    }
}
