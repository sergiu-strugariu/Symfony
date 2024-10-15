<?php

namespace App\Controller\Frontend;

use App\Entity\MembershipPackage;
use App\Entity\Payment;
use App\Entity\User;
use App\Helper\NetopiaHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Netopia\Payment\Address;
use Netopia\Payment\Invoice;
use Netopia\Payment\Request\Card;
use Netopia\Payment\Request\PaymentAbstract;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentController extends AbstractController
{
    /** @var string */
    private string $cipher;

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

    #[Route(path: '/order/{uuid}', name: 'app_payment')]
    public function payment(EntityManagerInterface $em, TranslatorInterface $translator, LoggerInterface $netopiaLogger, $uuid): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var Payment $payment */
        $payment = $em->getRepository(Payment::class)->findOneBy(['uuid' => $uuid]);

        // Check exist payment in DB
        if (empty($payment)) {
            // Set flash message and redirect
            $this->addFlash('danger', $translator->trans('form.default.required', [], 'messages'));
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
            $billingAddress->firstName = $user->getName();
            $billingAddress->lastName = $user->getSurname();
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
        } catch (\Exception $e) {
            // Set flash message and redirect
            $this->addFlash('danger', $translator->trans('form.default.required', [], 'messages'));
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

    #[Route(path: '/payment/ipn', name: 'app_payment_ipn')]
    public function ipn(Request $request, EntityManagerInterface $em, LoggerInterface $netopiaLogger): Response
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

                    $this->processNotification($paymentRequestIpn, $em, $netopiaLogger);
                } catch (\Exception $e) {
                    $this->errorType = PaymentAbstract::CONFIRM_ERROR_TYPE_TEMPORARY;
                    $this->errorCode = $e->getCode();
                    $this->errorMessage = $e->getMessage();
                    $netopiaLogger->error($this->errorMessage);
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
    
    #[Route(path: '/payment/confirm', name: 'app_payment_confirm')]
    public function confirm(Request $request, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        $orderId = $request->get('orderId');
        if (null === $orderId) {
            return $this->redirectToRoute('app_homepage');
        }
        
        $payment = $em->getRepository(Payment::class)->findOneBy(['uuid' => $orderId]);
        if (null === $payment) {
            return $this->redirectToRoute('app_homepage');
        }
        
        $paymentStatus = $payment->getStatus();
        $title = '';
        $subtitle = '';
        $error = false;
        
        switch ($paymentStatus)  {
            case Payment::PAYMENT_STATUS_PAID:
            case Payment::PAYMENT_STATUS_PENDING:
                $title = 'Mulţumim pentru comandă!';
                $subtitle = 'Plata este în curs de procesare. Dacă ai întrebări sau nevoie de asistență suplimentară, suntem aici să te ajutăm.';
                break;
            case Payment::PAYMENT_STATUS_CONFIRMED:
                $title = 'Mulţumim pentru comandă!';
                $subtitle = 'Vei primi în curând toate detaliile pe email. Dacă ai întrebări sau nevoie de asistență suplimentară, suntem aici să te ajutăm.';
                break;
            case Payment::PAYMENT_STATUS_CANCELED:
            case Payment::PAYMENT_STATUS_FAILED:
            default:
                $title = 'Ne pare rău, a apărut o eroare';
                $subtitle = 'Te rugăm să încerci din nou sau să ne contactezi pentru asistenţă. Suntem aici să te ajutăm şi ne dorim să rezolvăm situaţia cât mai rapid. Îți mulțumim pentru înţelegere!';
                $error = true;
                break;
        }
        
        return $this->render('frontend/payment/confirm.html.twig', [
            'title' => $title,
            'subtitle' => $subtitle,
            'error' => $error
        ]);
    }
    
    /**
     * Process the payment notification received from Netopia.
     */
    private function processNotification($paymentRequestIpn, $em, $netopiaLogger): void
    {
        $orderId = $paymentRequestIpn->orderId;
        $payment = $em->getRepository(Payment::class)->findOneBy(['uuid' => $orderId]);
        
        if (null === $payment) {
            $message = 'Payment not found';
            $this->setPermanentError(PaymentAbstract::ERROR_CONFIRM_INVALID_ACTION, $message);
            $netopiaLogger->error($message, ['uuid' => $orderId]);
        }
        
        if ($paymentRequestIpn->objPmNotify->errorCode == 0) {
            switch ($paymentRequestIpn->objPmNotify->action) {
                case 'confirmed':
                    $message = $paymentRequestIpn->objPmNotify->errorMessage;
                    $plan = $payment->getPlan();
                    $subscriptionExpireAt = $plan === MembershipPackage::MONTHLY ? new \DateTime('+1 month') : new \DateTime('+1 year');
                    // update DB: status = "confirmed/captured"
                    $payment->setStatus(Payment::PAYMENT_STATUS_CONFIRMED);
                    $payment->setPaymentMessage($message);
                    $payment->setPaymentToken($paymentRequestIpn->objPmNotify->token_id);
                    $payment->setPaymentTokenExpirationDate(new \DateTime($paymentRequestIpn->objPmNotify->token_expiration_date));
                    $payment->setSubscriptionExpireAt($subscriptionExpireAt);
                    // update membership on user
                    $user = $payment->getUser();
                    $user->setMembershipPackage($payment->getMembershipPackage());

                    $em->persist($payment);
                    $em->persist($user);
                    $em->flush();
                    $this->errorMessage = $message;
                    break;
                case 'paid_pending':
                case 'confirmed_pending':
                    $message = $paymentRequestIpn->objPmNotify->errorMessage;
                    // update DB: status = "pending"
                    $payment->setStatus(Payment::PAYMENT_STATUS_PENDING);
                    $payment->setPaymentMessage($message);
                    $em->persist($payment);
                    $em->flush();
                    $this->errorMessage = $message;
                    break;
                case 'paid':
                    $message = $paymentRequestIpn->objPmNotify->errorMessage;
                    // update DB: status = "open/preauthorized"
                    $payment->setStatus(Payment::PAYMENT_STATUS_PAID);
                    $payment->setPaymentMessage($message);
                    $em->persist($payment);
                    $em->flush();
                    $this->errorMessage = $message;
                    break;
                case 'canceled':
                    $message = $paymentRequestIpn->objPmNotify->errorMessage;
                    // update DB: status = "canceled"
                    $payment->setStatus(Payment::PAYMENT_STATUS_CANCELED);
                    $payment->setPaymentMessage($message);
                    $em->persist($payment);
                    $em->flush();
                    $this->errorMessage = $message;
                    break;
                case 'credit':
                    $message = $paymentRequestIpn->objPmNotify->errorMessage;
                    // update DB: status = "refunded"
                    $payment->setStatus(Payment::PAYMENT_STATUS_REFUNDED);
                    $payment->setPaymentMessage($message);
                    $em->persist($payment);
                    $em->flush();
                    $this->errorMessage = $message;
                    break;
                default:
                    $message = 'Invalid mobilpay reference action';
                    $this->setPermanentError(PaymentAbstract::ERROR_CONFIRM_INVALID_ACTION, $message);
                    $netopiaLogger->error($message, ['uuid' => $orderId]);
            }
        } else {
            $message = $paymentRequestIpn->objPmNotify->errorMessage;
            // update DB: status = "rejected"
            $payment->setStatus(Payment::PAYMENT_STATUS_FAILED);
            $payment->setPaymentMessage($message);
            $em->persist($payment);
            $em->flush();
            $this->errorMessage = $message;
            $netopiaLogger->error($message, ['uuid' => $orderId]);
        }
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
