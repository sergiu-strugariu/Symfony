<?php

namespace App\Controller\Frontend;

use App\Entity\EducationRegistration;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    #[Route('/contul-meu/contracte', name: 'app_my_account_contracts')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $contracts = $em->getRepository(EducationRegistration::class)->findBy([
            'user' => $user,
            'paymentStatus' => EducationRegistration::PAYMENT_STATUS_SUCCESS
        ]);

        return $this->render('frontend/default/account.html.twig', [
            'user' => $user,
            'contracts' => $contracts
        ]);
    }

    #[Route('/contul-meu/facturi', name: 'app_my_account_invoices')]
    public function invoices(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $invoices = $em->getRepository(EducationRegistration::class)->findBy([
            'user' => $user,
            'paymentStatus' => EducationRegistration::PAYMENT_STATUS_SUCCESS
        ]);

        return $this->render('frontend/default/account.html.twig', [
            'user' => $user,
            'invoices' => $invoices
        ]);
    }

    #[Route('/contul-meu/certificari', name: 'app_my_account_certifications')]
    public function certifications(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $certifications = $em->getRepository(EducationRegistration::class)->getCertifications($user);

        return $this->render('frontend/default/account.html.twig', [
            'user' => $user,
            'certifications' => $certifications
        ]);
    }

    #[Route('/contul-meu/detalii', name: 'app_my_account_details')]
    public function details(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        return $this->render('frontend/default/account.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/contul-meu/calendar', name: 'app_my_account_calendar')]
    public function calendar(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        $new = $em->getRepository(EducationRegistration::class)->findBy(
            [
                'user' => $user,
                'paymentStatus' => EducationRegistration::PAYMENT_STATUS_SUCCESS
            ],
            [
                'createdAt' => 'DESC'
            ]
        );

        $old = $em->getRepository(EducationRegistration::class)->findBy(
            [
                'user' => $user,
                'paymentStatus' => EducationRegistration::PAYMENT_STATUS_SUCCESS
            ],
            [
                'createdAt' => 'ASC'
            ]
        );

        $canceled = $em->getRepository(EducationRegistration::class)->findBy(
            [
                'user' => $user,
                'paymentStatus' => EducationRegistration::PAYMENT_STATUS_FAILED
            ]
        );

        $participated = $em->getRepository(EducationRegistration::class)->findBy(
            [
                'user' => $user,
                'paymentStatus' => EducationRegistration::PAYMENT_STATUS_SUCCESS
            ]
        );

        return $this->render('frontend/default/account.html.twig', [
            'user' => $user,
            'new' => $new,
            'old' => $old,
            'canceled' => $canceled,
            'participated' => $participated
        ]);
    }
    
    #[Route('/confirm-email-update/{token}', name: 'app_confirm_email_update')]
    public function confirmEmailUpdate(EntityManagerInterface $em, $token): RedirectResponse
    {
        $user = $em->getRepository(User::class)->findOneBy([
            'confirmationToken' => $token,
            'enabled' => true
        ]);

        if (null !== $user) {
            // update email
            $user->setEmail($user->getTempEmail());
            $user->setConfirmationToken(null);
            // save changes to db
            $em->persist($user);
            $em->flush();
        }

        return $this->redirectToRoute('app_index');
    }
}
