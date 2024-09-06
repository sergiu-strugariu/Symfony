<?php

namespace App\Command;

use App\Entity\EducationRegistration;
use App\Helper\MailHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:payment-reminder')]
class PaymentReminderCommand extends Command {
    
    protected static $defaultDescription = 'Sends an automated payment reminder.';
    
    public function __construct(
        private EntityManagerInterface $em,
        private MailHelper $mailHelper,
    ){
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $pendingPaymentsForReminder = $this->em->getRepository(EducationRegistration::class)->findPendingPaymentsForReminder();
        
        foreach ($pendingPaymentsForReminder as $pendingPayment) {
             $sent = $this->mailHelper->sendMail(
                $pendingPayment->getEmail(),
                'Notificare plata',
                'frontend/emails/payment-reminder.html.twig',
                [
                    'firstName' => $pendingPayment->getFirstName(),
                    'title' => $pendingPayment->getEducation()->getTranslation('ro')->getTitle()
                ]);

            if ($sent) {
                $pendingPayment->setReminderSent(true);
                
                $this->em->persist($pendingPayment);
                $this->em->flush();
            }
        }
        
        return Command::SUCCESS;
    }

}
