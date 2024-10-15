<?php

namespace App\Helper;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NetopiaHelper
{
    
    const SOAP_ENDPOINT = '/api/payment2/?wsdl';
    const USERNAME = 'sh.api';
    const PASSWORD = 'uJLyrH^@5gb=$Au';
    
    private $router;
    private $logger;
    private $baseUrl;
    private $signature;
    
    public function __construct(UrlGeneratorInterface $router, LoggerInterface $netopiaLogger, $baseUrl, $signature) {
        $this->router = $router;
        $this->logger = $netopiaLogger;
        $this->baseUrl = $baseUrl;
        $this->signature = $signature;
    }
    
    public function doPayT($payment) {
        $soap = new \SoapClient(sprintf('%s%s', $this->baseUrl, self::SOAP_ENDPOINT), ['cache_wsdl' => WSDL_CACHE_NONE]);

        $req = new \stdClass();

        $account = new \stdClass();
        $account->id = $this->signature;
        $account->user_name = self::USERNAME; // SOAP user
        $account->confirm_url = $this->router->generate('app_payment_ipn', [], UrlGeneratorInterface::ABSOLUTE_URL); // IPN URL
        
        $transaction = new \stdClass();
        $transaction->paymentToken = $payment->getPaymentToken();

        $orderId = $payment->getUuid();
        $userBilling = $payment->getUserBillingData();
        $user = $payment->getUser();
        
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

        $account->hash = strtoupper(sha1(strtoupper(md5(self::PASSWORD)) . "{$order->id}{$order->amount}{$order->currency}{$account->id}"));

        $req->account = $account;
        $req->order = $order;
        $req->transaction = $transaction;

        try {
            $response = $soap->doPayT(['request' => $req]);
            $result = $response->doPayTResult;
            if (isset($result->errors) && $result->errors->code != 0) {
                $this->logger->error($result->errors->message, ['uuid' => $orderId]);
                throw new \Exception($result->errors->message, $result->errors->code);
            }
        } catch (\SoapFault $e) {
            $message = $e->getMessage();
            $this->logger->error($message, ['uuid' => $orderId]);
            throw new \Exception($message);
        }
    }
    
    public function cancelToken($payment) {
        $soap = new \SoapClient(sprintf('%s%s', $this->baseUrl, self::SOAP_ENDPOINT), ['cache_wsdl' => WSDL_CACHE_NONE]);

        $orderId = $payment->getUuid();
        
        $req = new \stdClass();

        $account = new \stdClass();
        $account->id = $this->signature;
        $account->user_name = self::USERNAME; // SOAP user
        $account->confirm_url = $this->router->generate('app_payment_ipn', [], UrlGeneratorInterface::ABSOLUTE_URL); // IPN URL
        $account->hash = strtoupper(sha1(strtoupper(md5(self::PASSWORD)) . "{$account->id}"));

        $req->account = $account;
        $req->token = $payment->getPaymentToken();

        try {
            $response = $soap->cancelToken(['request' => $req]);
        } catch (\SoapFault $e) {
            $message = $e->getMessage();
            $this->logger->error($message, ['uuid' => $orderId]);
            throw new \Exception($message);
        }
    }

}