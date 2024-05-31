<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class CryptoController extends Controller
{
    public function createAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency' => [
                'required',
                'string',
                Rule::in(config('luno.supported_currencies')), // Ensure currency is supported
            ],
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $client = new Client([
            'curl' => [
                CURLOPT_DNS_SERVERS => '8.8.8.8, 8.8.4.4'
            ]
        ]);

    $apiKey = env('API_KEY');
    $apiSecret = env('API_SECRET_KEY');

    $url = "https://api.luno.com/api/1/accounts";

    $data = [
        'currency' => $request->currency,
        'name' => $request->name,
    ];

    $headers = [
        'Authorization' => "Basic " . base64_encode($apiKey . ':' . $apiSecret),
        'Content-Type' => 'application/json',
    ];

    try {
        \Log::info('URL: ' . $url);
        \Log::info('Headers: ' . json_encode($headers));
        \Log::info('Data: ' . json_encode($data));

        $response = $client->post($url, [
            'headers' => $headers,
            'json' => $data,
        ]);

        $data = json_decode($response->getBody(), true);

        if (isset($data['currency']) && isset($data['id']) && isset($data['name'])) {
            return response()->json([
                'success' => true,
                'account' => $data,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create account. Please check the Luno API response for details.',
            ], 422);
        }
    } catch (RequestException $e) {
        \Log::error('Request Exception: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage(),
        ], 500);
    } catch (Exception $e) {
        \Log::error('Exception: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'An error occurred: ' . $e->getMessage(),
        ], 500);
    }
}
}
