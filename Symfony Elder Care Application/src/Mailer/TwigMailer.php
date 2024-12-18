<?php

namespace App\Mailer;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class TwigMailer {

    /**
     * @var MailerInterface
     */
    protected $mailer;

    /**
     * @var UrlGeneratorInterface
     */
    protected $router;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * TwigSwiftMailer constructor.
     *
     * @param MailerInterface $mailer
     * @param UrlGeneratorInterface $router
     * @param array $parameters
     */
    public function __construct(MailerInterface $mailer, UrlGeneratorInterface $router, array $parameters) {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->parameters = $parameters;
    }
    
    public function sendForgotPasswordMessage($to, $subject, $context) {
        $template = 'frontend/emails/security/resetting.html.twig';

        return $this->sendMessage($template, $context, $this->parameters['from_email'], $this->parameters['from_sender'], $to, $subject);
    }

    public function sendActivatingAccountMessage($to, $subject, $context) {
        $template = 'frontend/emails/security/activate-account.html.twig';

        return $this->sendMessage($template, $context, $this->parameters['from_email'], $this->parameters['from_sender'], $to, $subject);
    }

    public function sendProspectOfferMessage($to, $subject, $message, $attachments, $cc, $bcc) {
        $template = 'shared/email/prospect_offer.html.twig';
        $context = [
            'message' => $message
        ];

        return $this->sendMessage($template, $context, $this->parameters['from_email'], $this->parameters['from_sender'], $to, $subject, $attachments, $cc, $bcc);
    }

    public function sendNpsMessage($to, $subject, $message) {
        $template = 'shared/email/nps.html.twig';
        $context = [
            'message' => $message
        ];

        return $this->sendMessage($template, $context, $this->parameters['from_email'], $this->parameters['from_sender'], $to, $subject);
    }

    /**
     * @param string $templateName
     * @param array $context
     * @param string $fromEmail
     * @param string $fromSender
     * @param string|array $to
     * @param string $subject
     */
    protected function sendMessage($templateName, $context, $fromEmail, $fromSender, $to, $subject, $attachments = [], $cc = [], $bcc = []) {
        $email = (new TemplatedEmail())
                ->from(new Address($fromEmail, $fromSender))
                ->subject($subject)
                ->htmlTemplate($templateName)
                ->context($context);
        
        if (is_array($to)) {
            $email->to(...$to);
        } else {
            $email->to($to);
        }
        
        if (!empty($cc)) {
            $email->cc(implode(',', $cc));
        }
        
        if (!empty($bcc)) {
            $email->bcc(implode(',', $bcc));
        }
        
        foreach ($attachments as $attachment) {
            $email->attachFromPath($attachment);
        }

        $sent = true;
        
        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
//            dd($e->getMessage());
            $sent = false;
        }

        return $sent;
    }

}
