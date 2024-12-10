<?php

namespace App\Helper;

use App\Entity\Payment;
use App\Entity\UserBillingData;
use DateTime;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;

class SmartBillAPIHelper
{
    const GENERATE_INVOICE_ENDPOINT = '/invoice';
    const GET_INVOICE_ENDPOINT = '/invoice/pdf';

    /**
     * @var string
     */
    private string $baseUrl;

    /**
     * @var string
     */
    private string $cif;

    /**
     * @var string
     */
    private string $invoiceSeriesName;

    /**
     * @var string
     */
    private string $username;

    /**
     * @var string
     */
    private string $token;

    protected LanguageHelper $language;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $smartbillLogger;

    /**
     * @param string $cif
     * @param string $invoiceSeriesName
     * @param string $username
     * @param string $token
     * @param string $baseUrl
     * @param LanguageHelper $language
     * @param LoggerInterface $smartbillLogger
     */
    public function __construct(string $cif, string $invoiceSeriesName, string $username, string $token, string $baseUrl, LanguageHelper $language, LoggerInterface $smartbillLogger)
    {
        $this->cif = $cif;
        $this->invoiceSeriesName = $invoiceSeriesName;
        $this->username = $username;
        $this->token = $token;
        $this->baseUrl = $baseUrl;
        $this->language = $language;
        $this->smartbillLogger = $smartbillLogger;
    }


    /**
     * @param Payment $payment
     * @param array $extraHeaders
     * @return array
     */
    #[ArrayShape(['status' => "bool", 'response' => "array|void"])]
    public function generateInvoice(Payment $payment, array $extraHeaders = []): array
    {
        $hasException = true;
        $response = [];

        try {
            $response = $this->sendRequest(
                'POST',
                self::GENERATE_INVOICE_ENDPOINT,
                $this->invoiceSeriesName,
                $this->populateData($payment),
                $extraHeaders
            );
        } catch (\Exception $e) {
            // Log the error and set the exception flag to true
            $this->smartbillLogger->error($e->getMessage(), ['uuid' => $payment->getUuid()]);
            $hasException = false;
        }

        // Check if 'series' and 'number' exist and are not empty using null coalescence
        $series = $response['series'] ?? null;
        $number = $response['number'] ?? null;

        if (!$hasException && (empty($series) || empty($number))) {
            $hasException = false;
        }

        return [
            'status' => $hasException,
            'response' => $response
        ];
    }

    /**
     * @param string $uuid
     * @param string $number
     * @param array $extraHeaders
     * @return array
     */
    #[ArrayShape(['status' => "bool", 'file' => "mixed"])]
    public function getInvoiceAsPDF(string $uuid, string $number, array $extraHeaders = []): array
    {
        $hasPdfException = true;
        $fileUrl = null;

        try {
            $fileUrl = $this->sendRequest('GET',
                sprintf('%s?cif=%s&seriesname=%s&number=%s', self::GET_INVOICE_ENDPOINT, $this->cif, $this->invoiceSeriesName, $number),
                $this->invoiceSeriesName, [], $extraHeaders, false
            );
        } catch (Exception $e) {
            $this->smartbillLogger->error($e->getMessage(), ['uuid' => $uuid]);
            $hasPdfException = false;
        }

        return [
            'status' => $hasPdfException,
            'file' => $fileUrl
        ];
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param string $seriesName
     * @param array $data
     * @param array $extraHeaders
     * @param bool $decodeResult
     * @return bool|mixed|string
     * @throws Exception
     */
    private function sendRequest(string $method, string $endpoint, string $seriesName = '', array $data = [], array $extraHeaders = [], bool $decodeResult = true): mixed
    {
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

    /**
     * @param string $username
     * @param string $token
     * @return string
     */
    private function generateAuthorization(string $username, string $token): string
    {
        return base64_encode(sprintf('%s:%s', $username, $token));
    }

    /**
     * @param Payment $payment
     * @return array
     */
    #[ArrayShape(['issueDate' => "string", 'isDraft' => "false", 'client' => "array", 'products' => "array[]", 'payment' => "array"])]
    public function populateData(Payment $payment): array
    {
        /** @var UserBillingData $billingData */
        $billingData = $payment->getUserBillingData();

        return [
            'issueDate' => (new DateTime())->format('Y-m-d'),
            'isDraft' => false,
            'client' => [
                'name' => $billingData->getCompanyName(),
                'vatCode' => $billingData->getCui(),
                'address' => $billingData->getAddress(),
                'country' => 'Romania',
                'county' => $billingData->getCounty()->getName(),
                'city' => $billingData->getCity()->getName(),
                'email' => $billingData->getEmail(),
                'saveToDb' => false
            ],
            'products' => [
                [
                    'name' => $payment->getMembershipPackage()->getTranslation($this->language->getDefaultLanguage()->getLocale())->getName(),
                    'measuringUnitName' => 'buc',
                    'currency' => 'RON',
                    'quantity' => 1,
                    'price' => $payment->getPrice(),
                    'isTaxIncluded' => false,
                    'taxPercentage' => 0,
                    'isService' => true,
                    'saveToDb' => false
                ]
            ],
            'payment' => [
                'value' => $payment->getPrice(),
                'type' => 'Card',
                'isCash' => false
            ]
        ];
    }
}