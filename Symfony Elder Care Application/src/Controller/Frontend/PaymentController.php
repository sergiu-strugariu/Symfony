<?php

namespace App\Controller\Frontend;

use App\Entity\MembershipPackage;
use App\Entity\Payment;
use App\Entity\User;
use App\Helper\MailHelper;
use App\Helper\NetopiaHelper;
use App\Helper\SmartBillAPIHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Netopia\Payment\Address;
use Netopia\Payment\Invoice;
use Netopia\Payment\Request\Card;
use Netopia\Payment\Request\PaymentAbstract;
use SoapFault;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractController
{
    /** @var string */
    private string $cipher;

    /**
     * @var null
     */
    private $iv;

    /** @var int */
    private int $errorType;

    /** @var int */
    private int $errorCode;

    /** @var string */
    private string $errorMessage;

    public function __construct()
    {
        $this->cipher = 'rc4';
        $this->iv = null;
        $this->errorType = PaymentAbstract::CONFIRM_ERROR_TYPE_NONE;
        $this->errorCode = 0;
        $this->errorMessage = '';
    }

    /**
     * @Route("/order/{uuid}", name="app_payment")
     */
    public function payment(EntityManagerInterface $em, LoggerInterface $netopiaLogger, $uuid): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Payment $payment */
        $payment = $em->getRepository(Payment::class)->findOneBy(['uuid' => $uuid]);

        // Check exist payment in DB
        if (empty($payment)) {
            // Set flash message and redirect
            $this->addFlash('error', 'Oops! Ceva nu a mers bine.');
            return $this->redirectToRoute('app_comand_detail', [
                'slug' => $payment->getMembershipPackage()->getSlug(),
                'packagePlan' => $payment->getPlan()
            ]);
        }

        try {
            // Create a payment request
            $paymentRequest = new Card();
            $paymentRequest->signature = $this->getParameter('netopia_signature');
            $paymentRequest->orderId = $payment->getUuid();
            $paymentRequest->confirmUrl = $this->generateUrl('app_payment_ipn', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $paymentRequest->returnUrl = $this->generateUrl('app_payment_confirm', [], UrlGeneratorInterface::ABSOLUTE_URL);

            // Invoice details
            $paymentRequest->invoice = new Invoice();
            $paymentRequest->invoice->currency = 'RON';
            $paymentRequest->invoice->amount = $payment->getPrice();
            $paymentRequest->invoice->details = $payment->getMembershipPackage()->getSlug();

            $userBilling = $payment->getUserBillingData();

            // Billing address
            $billingAddress = new Address();
            $billingAddress->type = 'company';
            $billingAddress->firstName = $user->getFirstName();
            $billingAddress->lastName = $user->getLastName();
            $billingAddress->address = $userBilling->getAddress();
            $billingAddress->email = $userBilling->getEmail();
            $billingAddress->mobilePhone = $userBilling->getPhone();
            $paymentRequest->invoice->setBillingAddress($billingAddress);

            // Encryption of payment data
            $paymentRequest->encrypt($this->getParameter('netopia_public_key_path'));

            // Get the encrypted data and environment key
            $envKey = $paymentRequest->getEnvKey();
            $data = $paymentRequest->getEncData();
            $cipher = $paymentRequest->getCipher();
            $iv = $paymentRequest->getIv();
        } catch (Exception $e) {
            // Set flash message and redirect
            $this->addFlash('error', 'Oops! Ceva nu a mers bine.');
            $netopiaLogger->error($e->getMessage(), ['uuid' => $uuid]);

            return $this->redirectToRoute('app_comand_detail', [
                'slug' => $payment->getMembershipPackage()->getSlug(),
                'packagePlan' => $payment->getPlan()
            ]);
        }

        // Redirects the user to Netopia payment page
        return $this->render('frontend/payment/payment_form.html.twig', [
            'env_key' => $envKey,
            'data' => $data,
            'cipher' => $cipher,
            'iv' => $iv,
            'payment_url' => $this->getParameter('netopia_url')
        ]);
    }

    /**
     * @Route("/payment/ipn", name="app_payment_ipn")
     */
    public function ipn(Request $request, EntityManagerInterface $em, LoggerInterface $netopiaLogger, SmartBillAPIHelper $smartBillAPIHelper, MailHelper $mail): Response
    {
        if ($request->isMethod('POST')) {
            $postData = $request->request->all();

            if (isset($postData['env_key']) && isset($postData['data'])) {
                // Set cipher and IV if provided
                if (isset($postData['cipher'])) {
                    $this->cipher = $postData['cipher'];
                    if (isset($postData['iv'])) {
                        $this->iv = $postData['iv'];
                    }
                }

                try {
                    // Process the payment notification
                    $paymentRequestIpn = PaymentAbstract::factoryFromEncrypted(
                        $postData['env_key'],
                        $postData['data'],
                        $this->getParameter('netopia_private_key_path'),
                        null,
                        $this->cipher,
                        $this->iv
                    );

                    // Process notification
                    $this->processNotification($paymentRequestIpn, $em, $netopiaLogger, $smartBillAPIHelper, $mail);
                } catch (Exception $e) {
                    $this->errorType = PaymentAbstract::CONFIRM_ERROR_TYPE_TEMPORARY;
                    $this->errorCode = $e->getCode();
                    $this->errorMessage = $e->getMessage();
                    $netopiaLogger->error($this->errorMessage);
                } catch (TransportExceptionInterface $e) {
                    $message = $e->getMessage();
                    $this->setPermanentError(PaymentAbstract::ERROR_CONFIRM_INVALID_POST_METHOD, $message);
                    $netopiaLogger->error($message);
                }
            } else {
                $message = 'mobilpay.ro posted invalid parameters';
                $this->setPermanentError(PaymentAbstract::ERROR_CONFIRM_INVALID_POST_PARAMETERS, $message);
                $netopiaLogger->error($message);
            }
        } else {
            $message = 'Invalid request method for payment confirmation';
            $this->setPermanentError(PaymentAbstract::ERROR_CONFIRM_INVALID_POST_METHOD, $message);
            $netopiaLogger->error($message);
        }

        return $this->generateXmlResponse();
    }

    /**
     * @Route("/payment/confirm", name="app_payment_confirm")
     */
    public function confirm(Request $request, EntityManagerInterface $em): Response
    {
        $orderId = $request->get('orderId');
        $error = false;

        if (null === $orderId) {
            return $this->redirectToRoute('app_homepage');
        }

        /** @var Payment $payment */
        $payment = $em->getRepository(Payment::class)->findOneBy(['uuid' => $orderId]);

        if (null === $payment) {
            return $this->redirectToRoute('app_homepage');
        }

        switch ($payment->getStatus()) {
            case Payment::PAYMENT_STATUS_PAID:
            case Payment::PAYMENT_STATUS_PENDING:
                $title = 'Mulțumim pentru comandă!';
                $subtitle = 'Plata este în curs de procesare. Dacă ai întrebări sau nevoie de asistență suplimentară, suntem aici să te ajutăm.';
                break;
            case Payment::PAYMENT_STATUS_CONFIRMED:
                $title = 'Mulțumim pentru comandă!';
                $subtitle = 'Vei primi în curând toate detaliile pe email. Dacă ai întrebări sau nevoie de asistență suplimentară, suntem aici să te ajutăm.';
                break;
            case Payment::PAYMENT_STATUS_CANCELED:
            case Payment::PAYMENT_STATUS_FAILED:
            default:
                $title = 'Ne pare rău, a apărut o eroare!';
                $subtitle = 'Te rugăm să încerci din nou sau să ne contactezi pentru asistență. Suntem aici să te ajutăm şi ne dorim să rezolvăm situația cât mai rapid. Îți mulțumim pentru înţelegere!';
                $error = true;
                break;
        }

        return $this->render('frontend/payment/order-status.html.twig', [
            'payment' => $payment,
            'title' => $title,
            'subtitle' => $subtitle,
            'error' => $error
        ]);
    }

    /**
     * @throws SoapFault
     * @throws TransportExceptionInterface
     * @Route("/dashboard/payment/cancel", name="app_payment_cancel")
     */
    public function cancel(NetopiaHelper $netopia, EntityManagerInterface $em, MailHelper $mail): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getMembershipPackage()->getSlug() === MembershipPackage::PACKAGE_FREE && $user->getPaymentToken() === null) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Oops! Ceva nu a mers bine.',
            ]);
        }

        /** @var NetopiaHelper $cancelResponse */
        $cancelResponse = $netopia->cancelToken($user->getPaymentToken());
        $expireDate = $user->getMembershipExpiresAt();
        $sendMail = false;

        if ($cancelResponse) {
            // Send confirm email
            $sendMail = $mail->sendMail(
                $user->getEmail(),
                'Anulare comandă',
                'frontend/emails/payment/email-order-cancel.html.twig',
                [
                    'pageTitle' => 'Anulare comandă',
                    'expireDate' => $expireDate->format('d M Y')
                ]
            );
        }

        // Check email success send
        if ($sendMail) {
            // Reset values
            $user->setPaymentToken(null);
            $user->setPaymentTokenExpirationDate(null);
            $user->setMembershipCancel(true);

            // Persist and save
            $em->persist($user);
            $em->flush();
        }

        return new JsonResponse([
            'status' => $sendMail,
            'message' => $sendMail ? sprintf('%expireDate', 'Membership-ul a fost anulat și va rămâne activ până la data de %expireDate.') : 'Oops! Ceva nu a mers bine.'
        ]);
    }


    /**
     * @Route("/dashboard/payment/invoice/{uuid}", name="app_payment_invoice")
     */
    public function getInvoice(SmartBillAPIHelper $helper, EntityManagerInterface $em, $uuid)
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Payment $payment */
        $payment = $em->getRepository(Payment::class)->findOneBy(['uuid' => $uuid, 'user' => $user]);

        // Check exist payment
        if ($payment === null) {
            return $this->redirectToRoute('dashboard_user_profile');
        }

        // Get number and name
        $invoiceNumber = $payment->getInvoiceNumber();
        $invoiceName = $payment->getInvoiceSeriesName();

        // Check exist values
        if ($invoiceNumber === null || $invoiceName === null) {
            $this->addFlash('error', 'Oops! Ceva nu a mers bine.');
            return $this->redirectToRoute('dashboard_user_profile');
        }

        // Generate pdf file
        $pdfResponse = $helper->getInvoiceAsPDF(
            $uuid,
            $payment->getInvoiceNumber(),
            ['Accept: application/octet-stream']
        );

        // Generate filename
        $fileName = sprintf('Factura_%s_%s.pdf', $payment->getInvoiceSeriesName(), $payment->getInvoiceNumber());

        // Check status generated
        if (!$pdfResponse['status'] || json_decode($pdfResponse['file']) !== null) {
            $this->addFlash('error', 'Oops! Ceva nu a mers bine.');
            return $this->redirectToRoute('dashboard_user_profile');
        }

        return new Response($pdfResponse['file'], Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $fileName)
        ]);
    }

    /**
     * Process the payment notification received from Netopia.
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    private function processNotification($paymentRequestIpn, $em, $netopiaLogger, SmartBillAPIHelper $smartBill, MailHelper $mail): void
    {
        // Get IPN @orderId
        $orderId = $paymentRequestIpn->orderId;

        /**
         * Get payment by @uuid
         * @var Payment $payment
         */
        $payment = $em->getRepository(Payment::class)->findOneBy(['uuid' => $orderId]);

        // Check exist payment
        if (null === $payment) {
            $message = 'Payment not found';
            $this->setPermanentError(PaymentAbstract::ERROR_CONFIRM_INVALID_ACTION, $message);
            $netopiaLogger->error($message, ['uuid' => $orderId]);
        }

        if ($paymentRequestIpn->objPmNotify->errorCode == 0) {
            switch ($paymentRequestIpn->objPmNotify->action) {
                case 'confirmed':
                    /** @var User $user */
                    $user = $payment->getUser();

//                    /**
//                     * Generate invoice by @payment
//                     * @var SmartBillAPIHelper $generateInvoice
//                     */
//                    $generateInvoice = $smartBill->generateInvoice($payment);
//
//                    // Check invoice status
//                    if ($generateInvoice['status']) {
//                        $payment->setInvoiceNumber($generateInvoice['response']['number']);
//                        $payment->setInvoiceSeriesName($generateInvoice['response']['series']);
//                    }

                    // Update DB: status = "confirmed/captured"
                    $membershipExpiresAt = $payment->getPlan() === MembershipPackage::MONTHLY ? new \DateTime('+1 month') : new \DateTime('+1 year');
                    $message = $paymentRequestIpn->objPmNotify->errorMessage;
                    $payment->setStatus(Payment::PAYMENT_STATUS_CONFIRMED);
                    $payment->setPaymentMessage($message);
                    $payment->setMembershipExpiresAt($membershipExpiresAt);
                    $payment->setUpdatedAt(new \DateTime());

                    // Update membership & token on user
                    $user->setMembershipPackage($payment->getMembershipPackage());
                    $user->setPaymentToken($paymentRequestIpn->objPmNotify->token_id);
                    $user->setPaymentTokenExpirationDate(new \DateTime($paymentRequestIpn->objPmNotify->token_expiration_date));
                    $user->setMembershipExpiresAt($membershipExpiresAt);
                    $user->setMembershipCancel(false);
                    $em->persist($user);

                    $em->persist($payment);
                    $em->flush();

//                    // Send confirm email
//                    $mail->sendMail(
//                        $user->getEmail(),
//                        'Confirmare comandă',
//                        'frontend/emails/payment/email-order-confirmation.html.twig',
//                        [
//                            'pageTitle' => 'Confirmare comandă',
//                            'payment' => $payment
//                        ]
//                    );
                    break;
                case 'paid_pending':
                case 'confirmed_pending':
                    // Update DB: status = "pending"
                    $message = $paymentRequestIpn->objPmNotify->errorMessage;
                    $payment->setStatus(Payment::PAYMENT_STATUS_PENDING);
                    $payment->setPaymentMessage($message);
                    break;
                case 'paid':
                    // Update DB: status = "open/preauthorized"
                    $message = $paymentRequestIpn->objPmNotify->errorMessage;
                    $payment->setStatus(Payment::PAYMENT_STATUS_PAID);
                    $payment->setPaymentMessage($message);
                    break;
                case 'canceled':
                    // Update DB: status = "canceled"
                    $message = $paymentRequestIpn->objPmNotify->errorMessage;
                    $payment->setStatus(Payment::PAYMENT_STATUS_CANCELED);
                    $payment->setPaymentMessage($message);
                    break;
                case 'credit':
                    // update DB: status = "refunded"
                    $message = $paymentRequestIpn->objPmNotify->errorMessage;
                    $payment->setStatus(Payment::PAYMENT_STATUS_REFUNDED);
                    $payment->setPaymentMessage($message);
                    break;
                default:
                    $message = 'Invalid mobilpay reference action';
                    $this->setPermanentError(PaymentAbstract::ERROR_CONFIRM_INVALID_ACTION, $message);
                    $netopiaLogger->error($message, ['uuid' => $orderId]);
            }
        } else {
            // Update DB: status = "rejected"
            $message = $paymentRequestIpn->objPmNotify->errorMessage;
            $payment->setStatus(Payment::PAYMENT_STATUS_FAILED);
            $payment->setPaymentMessage($message);

            // Set error in logger
            $netopiaLogger->error($message, ['uuid' => $orderId]);
        }

        $this->errorMessage = $message;
        $em->persist($payment);
        $em->flush();
    }

    /**
     * Set permanent error details.
     */
    private function setPermanentError($errorCode, $errorMessage): void
    {
        $this->errorType = PaymentAbstract::CONFIRM_ERROR_TYPE_PERMANENT;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Generate the XML response for Netopia.
     */
    private function generateXmlResponse(): Response
    {
        $xmlResponse = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        if ($this->errorCode == 0) {
            $xmlResponse .= "<crc>{$this->errorMessage}</crc>";
        } else {
            $xmlResponse .= "<crc error_type=\"{$this->errorType}\" error_code=\"{$this->errorCode}\">{$this->errorMessage}</crc>";
        }

        $response = new Response($xmlResponse);
        $response->headers->set('Content-Type', 'application/xml');
        return $response;
    }
}