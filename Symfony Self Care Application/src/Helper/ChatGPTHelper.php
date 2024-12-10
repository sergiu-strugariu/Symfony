<?php

namespace App\Helper;

use Exception;

class ChatGPTHelper
{
    /**
     * @var string
     */
    private string $apiUrl;

    /**
     * @var string
     */
    private string $apiKey;

    /**
     * @param string $apiUrl
     * @param string $apiKey
     */
    public function __construct(string $apiUrl, string $apiKey)
    {
        $this->apiUrl = $apiUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * @param $prompt
     * @param int $limit
     * @return mixed
     * @throws Exception
     */
    public function callOpenAiApi($prompt, int $limit = 50): mixed
    {
        // The payload message for the OpenAI API
        $data = [
            "model" => "gpt-4o-mini",
            "messages" => [
                ["role" => "system", "content" => "You are a helpful assistant that generates articles."],
                ["role" => "user", "content" => $prompt]
            ],
            "temperature" => 0.5,
            "max_tokens" => $limit,
            "frequency_penalty" => 0.5,
            "presence_penalty" => 0.5
        ];

        // Configuring the cURL request
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Execute the request and capture the response
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check for any errors
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new Exception("Curl error: " . $error_msg);
        }

        curl_close($ch);

        // Check the HTTP code and response
        if ($httpCode !== 200) {
            throw new Exception("Error in API call: HTTP code $httpCode, response: $response");
        }

        return json_decode($response, true);
    }
}