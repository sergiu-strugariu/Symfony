<?php

namespace App\Helper;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class MailHelper
{
    /**
     * @param MailerInterface $mailer
     * @param LoggerInterface $logger
     * @param $fromEmail
     * @param $fromSender
     */
    public function __construct(protected MailerInterface $mailer, protected LoggerInterface $logger, protected $fromEmail, protected $fromSender)
    {
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $template
     * @param array $data
     * @param array $attachments
     * @return bool
     * @throws TransportExceptionInterface
     */
    public function sendMail(string $to, string $subject, string $template, array $data = [], array $attachments = []): bool
    {
        $sent = true;
        $error = '';

        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromSender))
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($data);

        if (!empty($bcc)) {
            $email->addBcc($bcc);
        }

        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $email->addPart(new DataPart(new File($attachment)));
            }
        }

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            $sent = false;
            $error = $e->getMessage();
            dd($error);
        }

        // Set logger data
        $loggerData = [
            'email' => $to,
            'subject' => $subject
        ];

        if ($sent) {
            $this->logger->info('Mail successfully sent', $loggerData);
        } else {
            $this->logger->error('Mail not sent ' . $error, $loggerData);
        }

        return $sent;
    }
}