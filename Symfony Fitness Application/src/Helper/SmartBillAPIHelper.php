<?php

namespace App\Helper;

class SmartBillAPIHelper
{
   
    const GENERATE_INVOICE_ENDPOINT = '/invoice';
    const GENERATE_PROFORMA_ENDPOINT = '/estimate';
    const GET_INVOICE_ENDPOINT = '/invoice/pdf';
    const GET_PROFORMA_INVOICE_ENDPOINT = '/estimate/pdf';
    const GENERATE_STORNO_INVOICE_ENDPOINT = '/invoice/reverse';
    const INVOICE_TYPE_DEFAULT = 'invoice';
    const INVOICE_TYPE_PROFORMA = 'proforma';

    private $baseUrl = 'https://ws.smartbill.ro/SBORO/api';
    private $cif;
    private $invoiceSeriesName;
    private $proformaInvoiceSeriesName;
    private $username;
    private $token;

    public function __construct(string $cif, string $invoiceSeriesName, string $proformaInvoiceSeriesName, string $username, string $token) {
        $this->cif = $cif;
        $this->invoiceSeriesName = $invoiceSeriesName;
        $this->proformaInvoiceSeriesName = $proformaInvoiceSeriesName;
        $this->username = $username;
        $this->token = $token;
    }

    public function generateInvoice(string $type, array $data, array $extraHeaders = []) {
        $method = 'POST';
        $endpoint = self::GENERATE_INVOICE_ENDPOINT;
        $seriesName = $this->invoiceSeriesName;
        
        if ($type === self::INVOICE_TYPE_PROFORMA) {
            $endpoint = self::GENERATE_PROFORMA_ENDPOINT;
            $seriesName = $this->proformaInvoiceSeriesName;
        }
        
        return $this->sendRequest($method, $endpoint, $seriesName, $data, $extraHeaders);
    }
    
    public function getInvoiceAsPDF(string $type, string $number, array $extraHeaders = []) {
        $method = 'GET';
        $endpoint = self::GET_INVOICE_ENDPOINT;
        $seriesName = $this->invoiceSeriesName;
        
        if ($type === self::INVOICE_TYPE_PROFORMA) { 
            $endpoint = self::GET_PROFORMA_INVOICE_ENDPOINT;
            $seriesName = $this->proformaInvoiceSeriesName;
        }
        
        return $this->sendRequest($method, sprintf('%s?cif=%s&seriesname=%s&number=%s', $endpoint, $this->cif, $seriesName, $number), $seriesName, [], $extraHeaders, false);
    }
    
    public function generateStornoInvoice(array $data, array $extraHeaders = []) {
        $method = 'POST';
        $endpoint = self::GENERATE_STORNO_INVOICE_ENDPOINT;
        $seriesName = $this->invoiceSeriesName;
        
        return $this->sendRequest($method, $endpoint, $seriesName, $data, $extraHeaders);
    }

    private function sendRequest(string $method, string $endpoint, string $seriesName = '', array $data = [], array $extraHeaders = [], $decodeResult = true) {
        $authorization = $this->generateAuthorization($this->username, $this->token);

        $defaultHeaders = [
            sprintf('Authorization: Basic %s', $authorization),
            'Accept: application/json',
            'Content-Type: application/json'
        ];
        $headers = array_merge($defaultHeaders, $extraHeaders);
        $defaultData = [
            'companyVatCode' => $this->cif,
            'seriesName' => $seriesName
        ];

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, sprintf('%s%s', $this->baseUrl, $endpoint));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);

        if ($method == 'POST') {
            $finalData = array_merge($defaultData, $data);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($finalData));
        }

        $response = curl_exec($curl);

        if ($response === false) {
            // Handle cURL error
            $error = curl_error($curl);
            curl_close($curl);
            throw new \Exception(sprintf("cURL error: %s", $error));
        }

        curl_close($curl);
        
        if ($decodeResult) {
            $response = json_decode($response, true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \Exception('Invalid response from API');
            }
        }

        return $response;
    }

    private function generateAuthorization(string $username, string $token) {
        return base64_encode(sprintf('%s:%s', $username, $token));
    }

}