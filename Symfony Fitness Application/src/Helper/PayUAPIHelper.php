<?php

namespace App\Helper;

class PayUAPIHelper
{
   
    const AUTHORIZE_ENDPOINT = '/api/v4/payments/authorize';
    const PAYMENT_STATUS_ENDPOINT = '/api/v4/payments/status';
    
    private $baseUrl;
    private $secretKey;
    private $merchantCode;
    
    public function __construct(string $baseUrl, string $secretKey, string $merchantCode) {
        $this->baseUrl = $baseUrl;
        $this->secretKey = $secretKey;
        $this->merchantCode = $merchantCode;
    }
    
    public function authorizePayment(array $data, array $extraHeaders = []) {
        return $this->sendRequest('POST', self::AUTHORIZE_ENDPOINT, $data, $extraHeaders);
    }
    
    public function getPaymentStatus(string $merchantPaymentReference, array $extraHeaders = []) {
        return $this->sendRequest('GET', sprintf('%s/%s', self::PAYMENT_STATUS_ENDPOINT, $merchantPaymentReference), [], $extraHeaders);
    }
    
    private function sendRequest(string $method, string $endpoint, array $data = [], array $extraHeaders = []) {
        $date = date('c'); // ISO 8601 format
        $signature = $this->generateSignature($this->secretKey, $this->merchantCode, $date, $method, $endpoint, $data);
        
        $defaultHeaders = [
            sprintf('X-Header-Signature: %s', $signature), 
            sprintf('X-Header-Merchant: %s', $this->merchantCode), 
            sprintf('X-Header-Date: %s', $date), 
            'Content-Type: application/json'
        ];
        $headers = array_merge($defaultHeaders, $extraHeaders);

        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, sprintf('%s%s', $this->baseUrl, $endpoint));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);

        if ($response === false) {
            // Handle cURL error
            $error = curl_error($curl);
            curl_close($curl);
            throw new \Exception(sprintf("cURL error: %s", $error));
        }

        curl_close($curl);
        $result = json_decode($response, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception('Invalid response from API');
        }

        return $result;
    }
    
    private function generateSignature(string $secretKey, string $merchantCode, string $date, string $httpMethod, string $endpoint, array $data = []) {
        $encryptedData =  md5(!empty($data) ? json_encode($data): "");
        $payload = $merchantCode . $date . $httpMethod . $endpoint . $encryptedData;
        
        return hash_hmac('SHA256', $payload, $secretKey);
    }
    
}