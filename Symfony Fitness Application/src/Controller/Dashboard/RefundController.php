<?php

namespace App\Controller\Dashboard;

use App\Entity\Refund;
use App\Entity\EducationRegistration;
use App\Form\Type\RefundType;
use App\Helper\PayUAPIHelper;
use App\Helper\SmartBillAPIHelper;
use App\Helper\MailHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

class RefundController extends AbstractController
{
    #[Route('/dashboard/refund', name: 'app_dashboard_refund')]
    public function index(): Response
    {
        return $this->render('dashboard/refund/index.html.twig');
    }

    #[Route('/dashboard/refund/{uuid}/edit', name: 'dashboard_refund_edit')]
    public function edit(Request $request, EntityManagerInterface $em, $uuid): Response
    {
        $refund = $em->getRepository(Refund::class)->findOneBy(['uuid' => $uuid]);
        if (null === $refund) {
            return $this->redirectToRoute('app_dashboard_refund');
        }

        $form = $this->createForm(RefundType::class, $refund);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($refund);
            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully edited the refund.');
            return $this->redirectToRoute('app_dashboard_refund');
        }

        return $this->render('dashboard/refund/edit.html.twig', [
            'form' => $form->createView(),
            'entity' => $refund,
            'editMode' => true
        ]);
    }


    #[Route('/dashboard/refund/{uuid}/delete', name: 'dashboard_refund_delete')]
    public function delete(EntityManagerInterface $em, $uuid): Response
    {
        $refund = $em->getRepository(Refund::class)->findOneBy(['uuid' => $uuid]);
        if (null === $refund) {
            return $this->redirectToRoute('app_dashboard_refund');
        }

        $em->remove($refund);
        $em->flush();

        $this->addFlash('success', 'The refund has been successfully deleted');
        return $this->redirectToRoute('app_dashboard_refund');
    }
    
    #[Route('/dashboard/refund/{uuid}/approve', name: 'dashboard_refund_approve')]
    public function approve(EntityManagerInterface $em, PayUAPIHelper $payUAPIHelper, SmartBillAPIHelper $smartBillAPIHelper, MailHelper $mail, LoggerInterface $payuLogger, LoggerInterface $smartbillLogger, $uuid): Response
    {
        $refund = $em->getRepository(Refund::class)->findOneBy(['uuid' => $uuid]);
        if (null === $refund) {
            return $this->redirectToRoute('app_dashboard_refund');
        }
        
        if (Refund::STATUS_APPROVED === $refund->getStatus()) {
            $this->addFlash('danger', "You've already approved this refund");
            return $this->redirectToRoute('dashboard_refund_edit', ['uuid' => $uuid]);
        }

        $invoiceNumber = $refund->getInvoiceNumber();
        
        $educationRegistration = $em->getRepository(EducationRegistration::class)->findOneBy([
            'invoiceNumber' => $invoiceNumber,
            'paymentStatus' => EducationRegistration::PAYMENT_STATUS_SUCCESS
        ]);
        
        if (null === $educationRegistration) {
            $this->addFlash('danger', 'Registration not found');
            return $this->redirectToRoute('dashboard_refund_edit', ['uuid' => $uuid]);
        }
        
        $paymentMethod = $educationRegistration->getPaymentMethod();
        $payuPaymentReference = $refund->getPayuPaymentReference();
        
        if (null === $payuPaymentReference && $paymentMethod !== EducationRegistration::PAYMENT_TYPE_WIRE) {
            $this->addFlash('danger', 'Please complete first the PayU reference number');
            return $this->redirectToRoute('dashboard_refund_edit', ['uuid' => $uuid]);
        }
        
        $approvedAmount = $refund->getApprovedAmount();
        
        if (null === $approvedAmount) {
            $this->addFlash('danger', 'Please complete first the approved ammount');
            return $this->redirectToRoute('dashboard_refund_edit', ['uuid' => $uuid]);
        }
        
        if ($paymentMethod !== EducationRegistration::PAYMENT_TYPE_WIRE) {
            $payedAmount = $educationRegistration->getPaymentWithVAT();
            $payUData = [
                'payuPaymentReference' => $payuPaymentReference,
                'originalAmount' => $payedAmount,
                'currency' => 'RON',
                'amount' => $approvedAmount
            ];

            try {
                $payUResponse = $payUAPIHelper->refundPayment($payUData);
            } catch (\Exception $e) {
                $payuLogger->error($e->getMessage(), ['refund' => $refund->getId()]);
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('dashboard_refund_edit', ['uuid' => $uuid]);
            }

            if (isset($payUResponse['code']) && $payUResponse['code'] !== 200) {
                $payuLogger->error($payUResponse['message'], ['refund' => $refund->getId()]);
                $this->addFlash('danger', $payUResponse['message']);
                return $this->redirectToRoute('dashboard_refund_edit', ['uuid' => $uuid]);
            }
        }

        $isInvoicingPerLegalEntity = $educationRegistration->isInvoicingPerLegalEntity();
        $education = $educationRegistration->getEducation();
        $educationTranslation = $education->getTranslation($this->getParameter('default_locale'));
        $educationTitle = $educationTranslation->getTitle();
        $educationStartDate = $education->getStartDate()->format('d-m-Y');
        $educationEndDate = $education->getEndDate()->format('d-m-Y');
        $service = sprintf('%s (%s - %s)', $educationTitle, $educationStartDate, $educationEndDate);

        $smartBillData = [
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
                    'quantity' => -1,
                    'price' => $approvedAmount,
                    'isTaxIncluded' => true,
                    'taxPercentage' => $educationRegistration->getPaymentVat(),
                    'isService' => true,
                    'saveToDb' => false
                ]
            ]
        ];

        $hasException = false;
        try {
            $response = $smartBillAPIHelper->generateInvoice(SmartBillAPIHelper::INVOICE_TYPE_DEFAULT, $smartBillData);
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
                        $educationRegistration->getEmail(), 'Confirmare ramburs educatie', 'frontend/emails/email-notifications.html.twig', [
                    'title' => 'Confirmare ramburs educatie',
                    'name' => $refund->getFullName(),
                    'description' => "Am atasat factura storno pentru educatia ",
                    'educationName' => $education->getTranslation('ro')->getTitle(),
                    'generatedUrl' => $this->generateUrl('app_education_details', ['slug' => $education->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
                        ], $attachments
                );
            }
        }

        $refund->setStatus(Refund::STATUS_APPROVED);
        $em->persist($refund);
        $em->persist($educationRegistration);
        $em->flush();

        $this->addFlash('success', 'Congratulations, you have successfully approved the refund.');
        return $this->redirectToRoute('app_dashboard_refund');
    }
    
    #[Route('/dashboard/refund/{uuid}/reject', name: 'dashboard_refund_reject')]
    public function reject(EntityManagerInterface $em, $uuid): Response
    {
        $refund = $em->getRepository(Refund::class)->findOneBy(['uuid' => $uuid]);
        if (null === $refund) {
            return $this->redirectToRoute('app_dashboard_refund');
        }
        
        $refund->setStatus(Refund::STATUS_REJECTED);
        $em->persist($refund);
        $em->flush();

        $this->addFlash('success', 'Congratulations, you have successfully rejected the refund.');
        return $this->redirectToRoute('app_dashboard_refund');
    }
}
