<?php

namespace App\Controller\Frontend;

use App\Entity\EducationRegistration;
use App\Helper\SmartBillAPIHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IPNController extends AbstractController
{
    
    #[Route('/payu/ipn', name: 'app_payu_ipn')]
    public function payuIpn(Request $request, EntityManagerInterface $em, SmartBillAPIHelper $smartBillAPIHelper): Response
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        
        if (JSON_ERROR_NONE !== json_last_error()) {
            return new Response('');
        }
        
        if (isset($data['orderData']) && isset($data['authorization'])) {
            $orderData = $data['orderData'];
            $authorization = $data['authorization'];
            
            if (!isset($orderData['merchantPaymentReference'])) {
                return new Response('');
            }
            
            $educationRegistration = $em->getRepository(EducationRegistration::class)->find($orderData['merchantPaymentReference']);
            if (null === $educationRegistration) {
                return new Response('');
            }
            
            $educationRegistration->setPayuIpnRequest($data);
            $educationRegistration->setPayuPaymentReference($orderData['payuPaymentReference']);

            switch ($authorization['authorized']) {
                case 'SUCCESS':
                    $educationRegistration->setPaymentStatus(EducationRegistration::PAYMENT_STATUS_SUCCESS);
                    
                    $invoiceNumber = $educationRegistration->getInvoiceNumber();
                    $invoiceSeriesName = $educationRegistration->getInvoiceSeriesName();
                    
                    if (null === $invoiceNumber && null === $invoiceSeriesName) {
                        $education = $educationRegistration->getEducation();
                        $service = $education->getInvoiceServiceName();
                        $isInvoicingPerLegalEntity = $educationRegistration->isInvoicingPerLegalEntity();

                        $data = [
                            'issueDate' => (new \DateTime())->format('Y-m-d'),
                            'isDraft' => false,
                            'client' => [
                                'name' => $isInvoicingPerLegalEntity ? $educationRegistration->getCompanyName() : $educationRegistration->getFullName(),
                                'vatCode' => $isInvoicingPerLegalEntity ? $educationRegistration->getCui() : '',
                                'address' => $isInvoicingPerLegalEntity ? $educationRegistration->getCompanyAddress() : '',
                                'country' => 'Romania',
                                'email' => $educationRegistration->getEmail(),
                                'saveToDb' => false
                            ],
                            'products' => [
                                [
                                    'name' => $service,
                                    'measuringUnitName' => 'buc',
                                    'currency' => 'RON',
                                    'quantity' => 1,
                                    'price' => $educationRegistration->getPaymentAmount(),
                                    'isTaxIncluded' => false,
                                    'taxPercentage' => $educationRegistration->getPaymentVat(),
                                    'isService' => true,
                                    'saveToDb' => false
                                ]
                            ]
                        ];

                        $hasException = false;
                        try {
                            $response = $smartBillAPIHelper->generateInvoice(SmartBillAPIHelper::INVOICE_TYPE_DEFAULT, $data);
                        } catch (\Exception $e) {
                            $hasException = true;
                        }

                        if (!$hasException) {
                            if (isset($response['series']) && 
                                    isset($response['number']) &&
                                    !empty($response['series']) &&
                                    !empty($response['number'])) {
                                $educationRegistration->setInvoiceSeriesName($response['series']);
                                $educationRegistration->setInvoiceNumber($response['number']);
                            }
                        }
                    }
                    
                    break;
                case 'FAILED':
                    $educationRegistration->setPaymentStatus(EducationRegistration::PAYMENT_STATUS_FAILED);
                    
                    if (isset($authorization['responseMessage'])) {
                        $educationRegistration->setPaymentMessage($authorization['responseMessage']);
                    }
                    
                    break;
                default:
                    break;
            }

            $em->persist($educationRegistration);
            $em->flush();
        }
        
        return new Response('');
    }

}
