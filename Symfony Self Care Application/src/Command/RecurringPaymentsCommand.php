<?php

namespace App\Command;

use App\Entity\MembershipPackage;
use App\Entity\Payment;
use App\Entity\User;
use App\Helper\NetopiaHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RecurringPaymentsCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:recurring-payments';

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * @var NetopiaHelper
     */
    protected NetopiaHelper $netopiaHelper;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $paymentLogger;

    /**
     * @param EntityManagerInterface $em
     * @param NetopiaHelper $netopiaHelper
     * @param LoggerInterface $paymentLogger
     */
    public function __construct(EntityManagerInterface $em, NetopiaHelper $netopiaHelper, LoggerInterface $paymentLogger)
    {
        parent::__construct();
        $this->em = $em;
        $this->netopiaHelper = $netopiaHelper;
        $this->paymentLogger = $paymentLogger;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Processes the recurring payments');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var Payment $expiringPayments */
        $expiringPayments = $this->em->getRepository(Payment::class)->findExpiringPayments();

        /** @var MembershipPackage $freePackage */
        $freePackage = $this->em->getRepository(MembershipPackage::class)->findOneBy(['slug' => MembershipPackage::PACKAGE_FREE]);

        /** @var Payment $expiringPayment */
        foreach ($expiringPayments as $expiringPayment) {
            /** @var User $user */
            $user = $expiringPayment->getUser();

            // Check user canceled membership
            if ($user->isMembershipCancel()) {
                $message = sprintf('Successfully reset membership to user %s', $user->getUuid());
                $this->paymentLogger->info($message);

                // Change membership
                $user->setMembershipPackage($freePackage);
                $user->setMembershipExpiresAt(null);
                $user->setMembershipCancel(false);
                $expiringPayment->setProcessed(true);

                $this->em->persist($user);
                $this->em->persist($expiringPayment);
                $this->em->flush();
            } else {
                $payment = new Payment();
                $payment->setUser($expiringPayment->getUser());
                $payment->setUserBillingData($expiringPayment->getUserBillingData());
                $payment->setMembershipPackage($expiringPayment->getMembershipPackage());
                $payment->setPrice($expiringPayment->getPrice());
                $payment->setPlan($expiringPayment->getPlan());
                $payment->setStatus(Payment::PAYMENT_STATUS_PENDING);

                $expiringPayment->setProcessed(true);

                $this->em->persist($expiringPayment);
                $this->em->persist($payment);
                $this->em->flush();

                $paymentUuid = $payment->getUuid();

                try {
                    $this->netopiaHelper->doPayT($payment);
                } catch (\Exception $e) {
                    $message = sprintf('Error processing payment %s with error message: %s', $paymentUuid, $e->getMessage());
                    $this->paymentLogger->error($message);
                    $io->error($message);
                }

                $message = sprintf('Successfully processed payment %s', $paymentUuid);
                $this->paymentLogger->info($message);
            }

            $io->success($message);
        }

        return Command::SUCCESS;
    }
}
