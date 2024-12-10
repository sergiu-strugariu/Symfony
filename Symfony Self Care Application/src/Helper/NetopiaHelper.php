<?php

namespace App\Helper;

use Exception;
use Psr\Log\LoggerInterface;
use SoapFault;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NetopiaHelper
{
    const SOAP_ENDPOINT = '/api/payment2/?wsdl';

    /**
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $router;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var string
     */
    private string $username;

    /**
     * @var string
     */
    private string $password;

    /**
     * @var string
     */
    private string $baseUrl;

    /**
     * @var string
     */
    private string $signature;

    /**
     * @param UrlGeneratorInterface $router
     * @param LoggerInterface $netopiaLogger
     * @param string $baseUrl
     * @param string $signature
     * @param string $username
     * @param string $password
     */
    public function __construct(UrlGeneratorInterface $router, LoggerInterface $netopiaLogger, string $baseUrl, string $signature, string $username, string $password)
    {
        $this->router = $router;
        $this->logger = $netopiaLogger;
        $this->baseUrl = $baseUrl;
        $this->signature = $signature;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param $payment
     * @return void
     * @throws SoapFault
     * @throws Exception
     */
    public function doPayT($payment): void
    {
        $orderId = $payment->getUuid();
        $userBilling = $payment->getUserBillingData();
        $user = $payment->getUser();

        $soap = new \SoapClient(sprintf('%s%s', $this->baseUrl, self::SOAP_ENDPOINT), ['cache_wsdl' => WSDL_CACHE_NONE]);

        $req = new \stdClass();

        $account = new \stdClass();
        $account->id = $this->signature;
        $account->user_name = $this->username; // SOAP user
        $account->confirm_url = $this->router->generate('app_payment_ipn', [], UrlGeneratorInterface::ABSOLUTE_URL); // IPN URL

        $transaction = new \stdClass();
        $transaction->paymentToken = $user->getPaymentToken();

        $billing = new \stdClass();
        $billing->country = 'RO';
        $billing->county = $userBilling->getCounty()->getName();
        $billing->city = $userBilling->getCity()->getName();
        $billing->address = $userBilling->getAddress();
        $billing->postal_code = '-';
        $billing->first_name = $user->getName();
        $billing->last_name = $user->getSurname();
        $billing->phone = $userBilling->getPhone();
        $billing->email = $userBilling->getEmail();

        $order = new \stdClass();
        $order->id = $orderId;
        $order->description = $payment->getMembershipPackage()->getSlug();
        $order->amount = $payment->getPrice();
        $order->currency = 'RON';
        $order->billing = $billing;

        $account->hash = strtoupper(sha1(strtoupper(md5($this->password)) . "{$order->id}{$order->amount}{$order->currency}{$account->id}"));

        $req->account = $account;
        $req->order = $order;
        $req->transaction = $transaction;

        try {
            $response = $soap->doPayT(['request' => $req]);
            $result = $response->doPayTResult;
            if (isset($result->errors) && $result->errors->code != 0) {
                $this->logger->error($result->errors->message, ['uuid' => $orderId]);
                throw new Exception($result->errors->message, $result->errors->code);
            }
        } catch (SoapFault $e) {
            $message = $e->getMessage();
            $this->logger->error($message, ['uuid' => $orderId]);
            throw new Exception($message);
        }
    }

    /**
     * @throws SoapFault
     * @throws Exception
     */
    public function cancelToken($token): bool
    {
        $status = true;

        $soap = new \SoapClient(sprintf('%s%s', $this->baseUrl, self::SOAP_ENDPOINT), ['cache_wsdl' => WSDL_CACHE_NONE]);

        $req = new \stdClass();

        $account = new \stdClass();
        $account->id = $this->signature;
        $account->user_name = $this->username; // SOAP user
        $account->hash = strtoupper(sha1(strtoupper(md5($this->password)) . $token));

        $req->account = $account;
        $req->token = $token;

        try {
            $response = $soap->cancelToken(['request' => $req]);

            if (!isset($response->cancelTokenResult) || $response->cancelTokenResult != 1) {
                $this->logger->error($response, ['token' => $token]);
                throw new Exception($response);
            }
        } catch (SoapFault|Exception $e) {
            $status = false;
            $message = $e->getMessage();
            $this->logger->error($message, ['token' => $token]);
        }

        return $status;
    }
}