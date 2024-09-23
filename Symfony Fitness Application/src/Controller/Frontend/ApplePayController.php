<?php

namespace App\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApplePayController extends AbstractController {
    
    #[Route('/apple-pay/validate-session', name: 'app_apple_pay_validate_session')]
    public function validateSession(Request $request): Response {
        $params = json_decode($request->getContent(), true);
        
        if (!isset($params['url']) || empty($params['url'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid params'
            ]);
        }
        
        $appleUrl = $params['url'];
        $applePayMerchantId = $this->getParameter('apple_pay_merchant_id');
        $domainName = $request->getHost();
        // @TO DO - get certificate
        $appleCertificate = '';
        // @TO DO - get key
        $appleKey = '';

        $data = [
            'merchantIdentifier' => $applePayMerchantId,
            'domainName' => $domainName,
            'displayName' => 'Move On'
        ];

        try {
            $response = $this->sendRequest($appleUrl, $data, $appleCertificate, $appleKey);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'data' => $response
        ]);
    }

    private function sendRequest($url, $data, $appleCertificate, $appleKey) {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSLCERT, $appleCertificate); 
        curl_setopt($curl, CURLOPT_SSLKEY, $appleKey);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);

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

}
