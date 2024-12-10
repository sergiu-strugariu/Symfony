<?php

namespace App\Controller\Frontend;

use App\Entity\EducationRegistration;
use App\Helper\SmartBillAPIHelper;
use App\Helper\MailHelper;
use App\Helper\ZohoAPIHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

class IPNController extends AbstractController
{

    #[Route('/payu/ipn', name: 'app_payu_ipn')]
    public function payuIpn(Request $request, EntityManagerInterface $em, SmartBillAPIHelper $smartBillAPIHelper, MailHelper $mail, LoggerInterface $smartbillLogger, ZohoAPIHelper $zohoAPIHelper): Response
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return new Response('');
        }

        if (isset($data['orderData']) && isset($data['authorization']) && isset($data['paymentResult'])) {
            $orderData = $data['orderData'];
            $authorization = $data['authorization'];
            $paymentResult = $data['paymentResult'];

            if (!isset($orderData['merchantPaymentReference'])) {
                return new Response('');
            }


            $educationRegistration = $em->getRepository(EducationRegistration::class)->find($orderData['merchantPaymentReference']);
            if (null === $educationRegistration) {
                return new Response('');
            }

            if (EducationRegistration::PAYMENT_STATUS_SUCCESS === $educationRegistration->getPaymentStatus()) {
                return new Response('');
            }

            $installmentsNumber = isset($paymentResult['installmentsNumber']) ? $paymentResult['installmentsNumber'] : 1;

            $educationRegistration->setPayuIpnRequest($data);
            $educationRegistration->setPayuPaymentReference($orderData['payuPaymentReference']);
            $educationRegistration->setPayuPaymentInstallmentsNumber($installmentsNumber);

            switch ($authorization['authorized']) {
                case 'SUCCESS':
                    $educationRegistration->setPaymentStatus(EducationRegistration::PAYMENT_STATUS_SUCCESS);

                    $invoiceNumber = $educationRegistration->getInvoiceNumber();
                    $invoiceSeriesName = $educationRegistration->getInvoiceSeriesName();

                    $contractNumber = $em->getRepository(EducationRegistration::class)->findMaxContractNumber();
                    if ($contractNumber === null) {
                        $contractNumber = $this->getParameter('contract_number_start');
                    } else {
                        $contractNumber++;
                    }

                    if (null === $invoiceNumber && null === $invoiceSeriesName) {
                        $education = $educationRegistration->getEducation();
                        $user = $educationRegistration->getUser();
                        $educationTranslation = $education->getTranslation($this->getParameter('default_locale'));
                        $educationTitle = $educationTranslation->getTitle();
                        $educationStartDate = $education->getStartDate()->format('d-m-Y');
                        $educationEndDate = $education->getEndDate()->format('d-m-Y');
                        $service = sprintf('%s (%s - %s), contract nr.: %s, RRN: %s', $educationTitle, $educationStartDate, $educationEndDate, $contractNumber, $orderData['payuPaymentReference']);

                        $isInvoicingPerLegalEntity = $educationRegistration->isInvoicingPerLegalEntity();

                        $data = [
                            'issueDate' => (new \DateTime())->format('Y-m-d'),
                            'isDraft' => false,
                            'client' => [
                                'name' => $isInvoicingPerLegalEntity ? $educationRegistration->getCompanyName() : $educationRegistration->getFullName(),
                                'vatCode' => $isInvoicingPerLegalEntity ? $educationRegistration->getCui() : $educationRegistration->getCnp(),
                                'address' => $isInvoicingPerLegalEntity ? $educationRegistration->getCompanyAddress() : '',
                                'country' => 'Romania',
                                'county' => $educationRegistration->getCounty()->getName(),
                                'city' => $educationRegistration->getCity()->getName(),
                                'email' => $educationRegistration->getEmail(),
                                'saveToDb' => false
                            ],
                            'products' => [
                                [
                                    'name' => $service,
                                    'productDescription' => $education->getOmcCode(),
                                    'measuringUnitName' => 'buc',
                                    'currency' => 'RON',
                                    'quantity' => 1,
                                    'price' => $educationRegistration->getPaymentWithVAT(),
                                    'isTaxIncluded' => true,
                                    'taxPercentage' => $educationRegistration->getPaymentVat(),
                                    'isService' => true,
                                    'saveToDb' => false
                                ]
                            ],
                            'payment' => [
                                'value' => $educationRegistration->getPaymentWithVAT(),
                                'number' => $orderData['payuPaymentReference'],
                                'type' => $installmentsNumber > 1 ? 'Alta incasare' : 'Card',
                                'isCash' => false
                            ]
                        ];

                        $hasException = false;
                        try {
                            $response = $smartBillAPIHelper->generateInvoice(SmartBillAPIHelper::INVOICE_TYPE_DEFAULT, $data);
                        } catch (\Exception $e) {
                            $smartbillLogger->error($e->getMessage(), ['id' => $educationRegistration->getId()]);
                            $hasException = true;
                        }

                        if (!$hasException) {
                            if (isset($response['series']) &&
                                isset($response['number']) &&
                                !empty($response['series']) &&
                                !empty($response['number'])) {
                                $educationRegistration->setInvoiceSeriesName($response['series']);
                                $educationRegistration->setInvoiceNumber($response['number']);
                                $educationRegistration->setContractNumber($contractNumber);

                                $attachments = [];
                                $hasPdfException = false;

                                try {
                                    $extraHeaders = ['Accept: application/octet-stream'];
                                    $pdfResponse = $smartBillAPIHelper->getInvoiceAsPDF(SmartBillAPIHelper::INVOICE_TYPE_DEFAULT, $response['number'], $extraHeaders);
                                } catch (\Exception $e) {
                                    $smartbillLogger->error($e->getMessage(), ['id' => $educationRegistration->getId()]);
                                    $hasPdfException = true;
                                }

                                if (!$hasPdfException) {
                                    $attachments[] = [
                                        'file' => $pdfResponse,
                                        'name' => sprintf('%s %s %s', 'Factura', $response['series'], $response['number']),
                                        'mimeType' => 'application/pdf'
                                    ];
                                }

                                // send confirmation email
                                $mail->sendMail(
                                    $educationRegistration->getEmail(),
                                    'Confirmare inregistrare educatie',
                                    'frontend/emails/email-notifications.html.twig',
                                    [
                                        'title' => 'Confirmare inregistrare educatie',
                                        'name' => $user->getFullName(),
                                        'description' => "Te-ai inregistrat cu success la cursul ",
                                        'educationName' => $education->getTranslation('ro')->getTitle(),
                                        'generatedUrl' => $this->generateUrl('app_education_details', ['slug' => $education->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
                                    ],
                                    $attachments
                                );

                                $data = [
                                    'data' => [
                                        'IdEducationPurchase' => $educationRegistration->getId(),
                                        'PaymentMethod' => $educationRegistration->getEducationPaymentMethod(),
                                        'PaidValue' => $educationRegistration->getPaymentWithVAT(),
                                        'PaymentDate' => $education->getCreatedAt()->format('d-m-Y'),
                                        'Action' => 'PaymentCompleted'
                                    ]
                                ];

                                try {
                                    $zohoAPIHelper->sendRequest($data);
                                } catch (\Exception $exception) {}
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
