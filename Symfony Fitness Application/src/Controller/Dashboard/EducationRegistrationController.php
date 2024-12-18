<?php

namespace App\Controller\Dashboard;

use App\Entity\Education;
use App\Entity\EducationRegistration;
use App\Form\Type\EducationRegistrationType;
use App\Helper\FileUploader;
use App\Helper\SmartBillAPIHelper;
use App\Helper\MailHelper;
use App\Helper\ZohoAPIHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

class EducationRegistrationController extends AbstractController
{
    #[Route('/dashboard/education/{uuid}/registrations', name: 'dashboard_education_registrations')]
    public function registrations(EntityManagerInterface $em, $uuid)
    {
        $education = $em->getRepository(Education::class)->findOneBy(['uuid' => $uuid]);
        if (null === $education) {
            return $this->redirectToRoute('dashboard_index');
        }

        return $this->render('dashboard/education-registrations/index.html.twig', [
            'uuid' => $uuid
        ]);
    }

    #[Route('/dashboard/education/registration/{uuid}/edit', name: 'dashboard_education_registration_edit')]
    public function editRegistration(Request $request, EntityManagerInterface $em, SmartBillAPIHelper $smartBillAPIHelper, FileUploader $fileUploader, MailHelper $mail, LoggerInterface $smartbillLogger, ZohoAPIHelper $zohoAPIHelper, $uuid)
    {
        $educationRegistration = $em->getRepository(EducationRegistration::class)->findOneBy(['uuid' => $uuid]);
        if (null === $educationRegistration) {
            return $this->redirectToRoute('dashboard_index');
        }

        $educationUuid = $educationRegistration->getEducation()->getUuid();
        $paymentMethod = $educationRegistration->getPaymentMethod();
        $oldPaymentStatus = $educationRegistration->getPaymentStatus();

        $form = $this->createForm(EducationRegistrationType::class, $educationRegistration);
        $form->handleRequest($request);

        $contractNumber = $em->getRepository(EducationRegistration::class)->findMaxContractNumber();
        if ($contractNumber === null) {
            $contractNumber = $this->getParameter('contract_number_start');
        } else {
            $contractNumber++;
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if (EducationRegistration::PAYMENT_TYPE_WIRE === $paymentMethod) {
                $newPaymentStatus = $educationRegistration->getPaymentStatus();

                if ($oldPaymentStatus !== $newPaymentStatus && EducationRegistration::PAYMENT_STATUS_SUCCESS === $newPaymentStatus) {
                    $proformaInvoiceNumber = $educationRegistration->getProformaInvoiceNumber();
                    $proformaInvoiceSeriesName = $educationRegistration->getProformaInvoiceSeriesName();

                    if (null !== $proformaInvoiceNumber || null !== $proformaInvoiceSeriesName) {
                        $data = [
                            'issueDate' => (new \DateTime())->format('Y-m-d'),
                            'isDraft' => false,
                            'useEstimateDetails' => true,
                            'estimate' => [
                                'seriesName' => $proformaInvoiceSeriesName,
                                'number' => $proformaInvoiceNumber
                            ]
                        ];

                        $hasException = false;
                        try {
                            $response = $smartBillAPIHelper->generateInvoice(SmartBillAPIHelper::INVOICE_TYPE_DEFAULT, $data);
                        } catch (\Exception $e) {
                            $hasException = true;
                        }

                        if (!$hasException) {
                            if (isset($response['errorText']) && !empty($response['errorText'])) {
                                $this->addFlash('danger', $response['errorText']);
                                return $this->redirectToRoute('dashboard_education_registrations', [
                                            'uuid' => $educationUuid
                                ]);
                            }

                            if (isset($response['series']) && isset($response['number'])) {
                                $educationRegistration->setInvoiceSeriesName($response['series']);
                                $educationRegistration->setInvoiceNumber($response['number']);
                                $educationRegistration->setProformaInvoiceSeriesName(null);
                                $educationRegistration->setProformaInvoiceNumber(null);
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
                                
                                $user = $educationRegistration->getUser();
                                $education = $educationRegistration->getEducation();
                                
                                // send confirmation email
                                $mail->sendMail(
                                        $educationRegistration->getEmail(), 'Confirmare inregistrare educatie', 'frontend/emails/email-notifications.html.twig', [
                                    'title' => 'Confirmare inregistrare educatie',
                                    'name' => $user->getFullName(),
                                    'description' => "Te-ai inregistrat cu success la educatia ",
                                    'educationName' => $education->getTranslation('ro')->getTitle(),
                                    'generatedUrl' => $this->generateUrl('app_education_details', ['slug' => $education->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
                                        ], $attachments
                                );

                                $zohoData = [
                                    'data' => [
                                        'IdEducationPurchase' => $educationRegistration->getId(),
                                        'PaymentMethod' => $educationRegistration->getEducationPaymentMethod(),
                                        'PaidValue' => $educationRegistration->getPaymentWithVAT(),
                                        'PaymentDate' => $educationRegistration->getCreatedAt()->format('d-m-Y'),
                                        'Action' => 'PaymentCompleted'
                                    ]
                                ];

                                try {
                                    $zohoAPIHelper->sendRequest($zohoData);
                                } catch (\Exception $exception) {
                                }
                            }
                        }
                    }
                }
            }

            $file = $form->get('certificateFileName')->getData();
            if ($file instanceof UploadedFile) {
                $uploadFile = $fileUploader->uploadFile($file, $form, $this->getParameter('app_diploma_path'));
                if ($uploadFile['success']) {
                    $educationRegistration->setCertificateFileName($uploadFile['fileName']);
                }
            }

            $em->persist($educationRegistration);
            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully edited the registration.');
            return $this->redirectToRoute('dashboard_education_registrations', [
                        'uuid' => $educationUuid
            ]);
        }

        return $this->render('dashboard/education-registrations/management.html.twig', [
                    'form' => $form->createView(),
                    'entity' => $educationRegistration,
                    'editMode' => true
        ]);
    }
}
