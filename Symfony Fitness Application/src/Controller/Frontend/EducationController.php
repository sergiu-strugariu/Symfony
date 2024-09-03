<?php

namespace App\Controller\Frontend;

use App\Entity\Education;
use App\Entity\EducationRegistration;
use App\Entity\Page;
use App\Form\Type\FormRegisterType;
use App\Helper\LanguageHelper;
use App\Helper\MailHelper;
use App\Helper\PayUAPIHelper;
use App\Helper\SmartBillAPIHelper;
use App\Repository\EducationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class EducationController extends AbstractController
{
    
    #[Route('/calendar-cursuri', name: 'app_educations')]
    public function listCourses(Request $request, EntityManagerInterface $em, EducationRepository $repository, LanguageHelper $languageHelper): Response
    {
        $locale = $request->getLocale();
        $language = $languageHelper->getLanguageByLocale($locale);

        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "courses-calendar"]);

        $type = $request->get('type');
        $location = $request->get('location');
        $query = $request->get('q');

        $locations = $repository->getAllCourseLocations($type);
        if (!in_array($location, $locations))  {
            $location = "all";
        }

        $courses = $repository->findCoursesByFilters($language, $type, $location, $query);
        $types = $repository->getAllCourseTypes();

        return $this->render('frontend/default/page.html.twig', [
            'page' => $page,
            'courses' => $courses,
            'locations' => $locations,
            'types' => array_column($types, 'type'),
            'selectedType' => $type,
            'selectedLocation' => $location,
            'query' => $query
        ]);
    }

    #[Route('/educatie/{slug}', name: 'app_education_details')]
    public function educationDetails(EntityManagerInterface $em, LanguageHelper $helper ,$slug): Response
    {
        $education = $em->getRepository(Education::class)->findOneBy(['slug' => $slug]);
        $locale = $helper->getLocaleFromRequest();
        if (null === $education) {
           return $this->redirectToRoute('app_educations'); 
        }

        return $this->render('frontend/education/details.html.twig', [
            'education' => $education,
            'locale' => $locale
        ]);
    }
    
    #[Route('/educatie/{slug}/inregistrare', name: 'app_education_register')]
    public function educationRegister(Request $request, EntityManagerInterface $em, MailHelper $mail, TranslatorInterface $translator, PayUAPIHelper $payUAPIHelper, SmartBillAPIHelper $smartBillAPIHelper, $slug): Response
    {
        $education = $em->getRepository(Education::class)->findOneBy(['slug' => $slug]);
        if (null === $education) {
            $this->addFlash('error', "Educatia nu a fost gasita.");
            return $this->redirectToRoute('app_educations');
        }
        
        $user = $this->getUser();
        if (null === $user) {
            $this->addFlash('error', "Trebuie sa fii logat ca sa te poti inregistra la o educatie");
            return $this->redirectToRoute('app_login');
        }

        $educationRegistration = new EducationRegistration();
        $form = $this->createForm(FormRegisterType::class, $educationRegistration, [
            'user' => $user
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $existingRegistration = $em->getRepository(EducationRegistration::class)->findOneBy([
                    'user' => $user,
                    'education' => $education
                ]);

                if (null !== $existingRegistration) {
                    $this->addFlash('error', "Deja esti inscris in aceasta educatie.");
                    return $this->redirectToRoute('app_educations');
                }

                $uuid = Uuid::v4();
                $educationVat = $education->getVat();

                $educationRegistration->setUuid($uuid);
                $educationRegistration->setEducation($education);
                $educationRegistration->setUser($user);
                $educationRegistration->setContract(true);
                $educationRegistration->setPaymentStatus(EducationRegistration::PAYMENT_STATUS_PENDING);
                $educationRegistration->setPaymentAmount($education->getBasePrice());
                $educationRegistration->setPaymentVat($educationVat);

                $em->persist($educationRegistration);
                $em->flush();
                
                if (EducationRegistration::PAYMENT_TYPE_WIRE == $educationRegistration->getPaymentMethod()) {
                    $service = $education->getInvoiceServiceName();
                    $isInvoicingPerLegalEntity = $educationRegistration->isInvoicingPerLegalEntity();
                    
                    $data = [
                        'issueDate' => (new \DateTime())->format('Y-m-d'),
                        'dueDate' => (new \DateTime('+5 days'))->format('Y-m-d'),
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

                    try {
                        $response = $smartBillAPIHelper->generateInvoice(SmartBillAPIHelper::INVOICE_TYPE_PROFORMA, $data);
                    } catch (\Exception $e) {
                       $this->addFlash('danger', $e->getMessage());
                       return $this->redirectToRoute('app_education_register', ['slug' => $slug]);
                    }
                    
                    if (isset($response['errorText']) && !empty($response['errorText'])) {
                        $this->addFlash('danger', $response['errorText']);
                        return $this->redirectToRoute('app_education_register', ['slug' => $slug]);
                    }
                    
                    if (isset($response['series']) && isset($response['number'])) {
                        $educationRegistration->setProformaInvoiceSeriesName($response['series']);
                        $educationRegistration->setProformaInvoiceNumber($response['number']);
                        
                        $em->persist($educationRegistration);
                        $em->flush();
                        
                        $attachments = [];
                        $hasException = false;
                        
                        try {
                            $extraHeaders = ['Accept: application/octet-stream'];
                            $pdfResponse = $smartBillAPIHelper->getInvoiceAsPDF(SmartBillAPIHelper::INVOICE_TYPE_PROFORMA, $response['number'], $extraHeaders);
                        } catch (\Exception $e) {
                            $hasException = true;
                        }
                        
                        if (!$hasException) {
                            $attachments[] = [
                                'file' => $pdfResponse,
                                'name' => sprintf('%s %s %s', 'Factura proforma', $response['series'], $response['number']),
                                'mimeType' => 'application/pdf'
                            ];
                            // send proforma email
                            $mail->sendMail(
                                    $educationRegistration->getEmail(), 
                                    $translator->trans('mails.wire_transfer.title'),
                                    'frontend/emails/wire-transfer.html.twig', 
                                    [
                                        'title' => 'Factura proforma',
                                        'education' => $education,
                                        'educationRegistration' => $educationRegistration
                                    ], 
                                    $attachments
                            );
                        }
                    }

                    return $this->redirectToRoute('app_education_registration_wire_transfer', ['slug' => $slug, 'uuid' => $uuid]);
                }

                $defaultLocale = $this->getParameter('default_locale');
                $data = [
                    'merchantPaymentReference' => $educationRegistration->getId(),
                    'currency' => 'RON',
                    'returnUrl' => $this->generateUrl('app_education_registration_details', ['slug' => $slug, 'uuid' => $uuid], UrlGeneratorInterface::ABSOLUTE_URL),
                    'authorization' => [
                        'paymentMethod' => 'CCVISAMC',
                        'installmentsNumber' => 1,
                        'usePaymentPage' => 'YES'
                    ],
                    'client' => [
                        'billing' => [
                            'firstName' => $educationRegistration->getFirstName(),
                            'lastName' => $educationRegistration->getLastName(),
                            'email' => $educationRegistration->getEmail(),
                            'phone' => $educationRegistration->getPhone(),
                            'countryCode' => 'RO'
                        ],
                        'delivery' => [
                            'firstName' => $educationRegistration->getFirstName(),
                            'lastName' => $educationRegistration->getLastName(),
                            'email' => $educationRegistration->getEmail(),
                            'phone' => $educationRegistration->getPhone(),
                            'countryCode' => 'RO'
                        ]
                    ],
                    'products' => [
                        [
                            'name' => $education->getTranslation($defaultLocale)->getTitle(),
                            'sku' => $education->getId(),
                            'unitPrice' => $education->getPriceWithVAT(),
                            'quantity' => 1,
                            'vat' => $educationVat
                        ]
                    ]
                ];

                if ($educationRegistration->isInvoicingPerLegalEntity()) {
                    $data['client']['billing']['companyName'] = $educationRegistration->getCompanyName();
                    $data['client']['billing']['taxId'] = $educationRegistration->getCui();
                    $data['client']['billing']['addressLine1'] = $educationRegistration->getCompanyAddress();
                }

                $hasException = false;
                try {
                    $response = $payUAPIHelper->authorizePayment($data);
                } catch (\Exception $e) {
                    $hasException = true;
                }

                if ($hasException) {
                    $this->addFlash('error', $e->getMessage());
                    return $this->redirectToRoute('app_education_register', ['slug' => $slug]);
                }

                if (isset($response['code']) && $response['code'] == 200) {
                    if (isset($response['paymentResult']) && isset($response['paymentResult']['url'])) {
                        return new RedirectResponse($response['paymentResult']['url']);
                    }

                    // send confirmation email
                    /*$mail->sendMail(
                            $educationRegistration->getEmail(),
                            'Inregistrare Curs', 
                            'frontend/emails/email-notifications.html.twig', 
                            [
                                'name' => $user->getFullName(),
                                'description' => "Te-ai inregistrat cu success la cursul ",
                                'educationName' => $education->getTranslation('ro')->getTitle(),
                                'generatedUrl' => $this->generateUrl('app_education_details', ['slug' => $education->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL)
                            ]
                    );*/
                } else {
                    $this->addFlash('error', $response['message']);
                    return $this->redirectToRoute('app_education_register', ['slug' => $slug]);
                }
            }
        }

        return $this->render('frontend/education/register.html.twig', [
            'education' => $education,
            'form' => $form->createView()
        ]);
    }
    
    #[Route('/educatie/{slug}/inregistrare/{uuid}', name: 'app_education_registration_details')]
    public function educationRegistrationDetails(EntityManagerInterface $em, PayUAPIHelper $payUAPIHelper, TranslatorInterface $translator, $slug, $uuid): Response
    {
        $education = $em->getRepository(Education::class)->findOneBy(['slug' => $slug]);
        if (null === $education) {
           return $this->redirectToRoute('app_educations'); 
        }
        
        $educationRegistration = $em->getRepository(EducationRegistration::class)->findOneBy(['uuid' => $uuid]);
        if (null === $educationRegistration) {
            return $this->redirectToRoute('app_education_details', ['slug' => $slug]);
        }

        $error = false;
        $title = '';
        $message = '';
        
        try {
            $response = $payUAPIHelper->getPaymentStatus($educationRegistration->getId());
        } catch (\Exception $e) {
            $error = true;
            $title = 'Oups, a intervenit o eroare neprevăzută';
            $message = $e->getMessage();
        }
        
        if (isset($response['code']) && $response['code'] == 200) {
            if (isset($response['authorizations']) && is_array($response['authorizations'])) {
                $authorization = reset($response['authorizations']);
                switch ($authorization['authorized']) {
                    case 'SUCCESS':
                        $title = $translator->trans('education.success.title');
                        $message = $translator->trans('education.success.message');
                        break;
                    case 'FAILED':
                        $error = true;
                        $title = $translator->trans('education.failed.title');
                        $message = $authorization['responseMessage'];
                        break;
                    case 'PENDING':
                        $title = $translator->trans('education.pending.title');
                        $message = '';
                        break;
                    default:
                        break;
                }
            }
        } else {
            $error = true;
            $title = $translator->trans('authentication.account.default_password_error');
            $message = $response['message'];
        }

        return $this->render('frontend/education/result.html.twig', [
            'education' => $education,
            'slug' => $slug,
            'uuid' => $uuid,
            'error' => $error,
            'title' => $title,
            'message' => $message
        ]);
    }
    
    #[Route('/educatie/{slug}/inregistrare/{uuid}/transfer-bancar', name: 'app_education_registration_wire_transfer')]
    public function educationRegistrationWireTransfer(EntityManagerInterface $em, $slug, $uuid): Response
    {
        $education = $em->getRepository(Education::class)->findOneBy(['slug' => $slug]);
        if (null === $education) {
           return $this->redirectToRoute('app_educations'); 
        }
        
        $educationRegistration = $em->getRepository(EducationRegistration::class)->findOneBy(['uuid' => $uuid]);
        if (null === $educationRegistration) {
            return $this->redirectToRoute('app_education_details', ['slug' => $slug]);
        }

        return $this->render('frontend/education/wire_transfer.html.twig', [
            'education' => $education,
            'educationRegistration' => $educationRegistration
        ]);
    }
    
    #[Route('/educatie/{slug}/inregistrare/{uuid}/contract', name: 'app_education_registration_contract')]
    public function educationRegistrationContract(EntityManagerInterface $em, $slug, $uuid): Response
    {
        $education = $em->getRepository(Education::class)->findOneBy(['slug' => $slug]);
        if (null === $education) {
           return new Response('');
        }
        
        $educationRegistration = $em->getRepository(EducationRegistration::class)->findOneBy(['uuid' => $uuid]);
        if (null === $educationRegistration) {
            return new Response('');
        }

        $defaultLocale = $this->getParameter('default_locale');
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $html = $this->renderView('frontend/contract/_template.html.twig', [
            'education' => $education,
            'educationRegistration' => $educationRegistration
        ]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream(sprintf('Contract %s.pdf', $education->getTranslation($defaultLocale)->getTitle()), ["Attachment" => true]);
    }
    
    #[Route('/educatie/{slug}/inregistrare/{uuid}/factura', name: 'app_education_registration_invoice')]
    public function educationRegistrationInvoice(EntityManagerInterface $em, SmartBillAPIHelper $smartBillAPIHelper, $slug, $uuid): Response
    {
        $education = $em->getRepository(Education::class)->findOneBy(['slug' => $slug]);
        if (null === $education) {
            return new Response('');
        }
        
        $educationRegistration = $em->getRepository(EducationRegistration::class)->findOneBy(['uuid' => $uuid]);
        if (null === $educationRegistration) {
            return new Response('');
        }

        $invoiceNumber = $educationRegistration->getInvoiceNumber();
        $invoiceSeriesName = $educationRegistration->getInvoiceSeriesName();
        $type = SmartBillAPIHelper::INVOICE_TYPE_DEFAULT;

        if (null === $invoiceNumber && null === $invoiceSeriesName) {
            $invoiceNumber = $educationRegistration->getProformaInvoiceNumber();
            $invoiceSeriesName = $educationRegistration->getProformaInvoiceSeriesName();
            $type = SmartBillAPIHelper::INVOICE_TYPE_PROFORMA;
        }

        if (null !== $invoiceNumber && null !== $invoiceSeriesName) {
            $fileName = sprintf('Factura_%s_%s.pdf', $invoiceSeriesName, $invoiceNumber);

            try {
                $extraHeaders = ['Accept: application/octet-stream'];
                $pdfResponse = $smartBillAPIHelper->getInvoiceAsPDF($type, $invoiceNumber, $extraHeaders);
            } catch (\Exception $e) {
                return new Response('');
            }

            return new Response($pdfResponse, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName)
            ]);
        }

        return new Response('');
    }
    
}
