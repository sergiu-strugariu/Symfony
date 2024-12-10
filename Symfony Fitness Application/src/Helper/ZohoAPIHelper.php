<?php

namespace App\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;

class ZohoAPIHelper extends AbstractController
{
    const ERROR_MESSAGES = [
        'INVALID_REQUEST' => 'Unable to process your request. Please verify whether you have entered the proper method name, parameter, and parameter values.',
        'BAD_REQUEST' => 'Invalid request body: empty or null.',
        'INVALID_TOKEN' => 'Invalid OAuth token.',
        'TOO_MANY_REQUESTS' => 'You have reached your API call limit for a minute. Please try again later.'
    ];
    const EDUCATION_MAPPING = [
        'course' => 'Certificare',
        'workshop' => 'Workshop',
        'convention' => 'Event'
    ];

    const ZOHO_ENDPOINT = "/crm/v2/functions/getdatafromwebsite/actions/execute";

    private $baseUrl = 'https://www.zohoapis.eu';
    private $apiKey;
    private $zohoLogger;

    public function __construct($zohoApiKey, LoggerInterface $zohoLogger)
    {
        $this->apiKey = $zohoApiKey;
        $this->zohoLogger = $zohoLogger;
    }

    public function sendRequest(array $data)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, sprintf('%s%s?auth_type=apikey&zapikey=%s', $this->baseUrl, self::ZOHO_ENDPOINT, $this->apiKey));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new \Exception(sprintf("cURL error: %s", $error));
        }

        curl_close($curl);

        $response = json_decode($response, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception('Invalid response from API');
        }

        if (isset($response['code']) && $response['code'] != 'success') {
            $this->zohoLogger->error('Zoho API error', $response);
            throw new \Exception($response['message']);
        }
    }
    
    public function getEducationMappingByType($type) {
        $educationMapping = self::EDUCATION_MAPPING;
        
        $mapping = '';
        if (isset($educationMapping[$type])) {
            $mapping = $educationMapping[$type];
        }
        
        return $mapping;
    }
}