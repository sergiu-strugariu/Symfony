<?php

namespace App\Controller\Frontend;

use App\Entity\Page;
use App\Entity\User;
use App\Form\LoginType;
use App\Form\Type\UserType;
use App\Helper\MailHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    #[Route('/register', name: 'app_register')]
    public function authentication(Request $request, AuthenticationUtils $authenticationUtils, MailHelper $mail, EntityManagerInterface $em, TranslatorInterface $translator, Security $security, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $page = $em->getRepository(Page::class)->findOneBy(['machineName' => "authentication"]);

        $user = new User();
        $registerForm = $this->createForm(UserType::class, $user);
        $registerForm->handleRequest($request);

        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            $user->setUuid(Uuid::v4());

            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $registerForm->get('email')->getData()]);
            if ($existingUser) {
                $this->addFlash('error', $translator->trans('authentication.exists'));
                return $this->redirectToRoute('app_login');
            }

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $registerForm->get('plainPassword')->getData()
                )
            );

            $user->setEnabled(false);
            $user->setRole("ROLE_CLIENT");

            $email = $mail->sendMail(
                $user->getEmail(),
                'Confirmare cont',
                'frontend/emails/email-account-confirmation.html.twig', [
                    'title' => $translator->trans('mails.email_account_confirmation.confirm_account'),
                    'name' => $user->getFullName(),
                    'generatedUrl' => $this->generateUrl('app_account_confirmation', [
                        'uuid' => $user->getUuid()
                    ], UrlGeneratorInterface::ABSOLUTE_URL)
                ]
            );

            if (!$email) {
                $this->addFlash('error', $translator->trans('authentication.mail.fail'));
                return $this->redirectToRoute('app_login');
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $translator->trans('authentication.success'));
            return $this->redirectToRoute('app_login');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('frontend/default/page.html.twig', [
            'page' => $page,
            'registerForm' => $registerForm->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/confirmare-cont/{uuid}', name: 'app_account_confirmation')]
    public function confirm(EntityManagerInterface $em, TranslatorInterface $translator, $uuid): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['uuid' => $uuid]);

        if (!$user) {
            $this->addFlash('error', $translator->trans('authentication.account.not_exists'));
            return $this->redirectToRoute('app_login');
        }

        if ($user->isEnabled()) {
            $this->addFlash('success', $translator->trans('authentication.account.already_confirmed'));
            return $this->redirectToRoute('app_login');
        }

        $user->setEnabled(true);
        $user->setConfirmationToken(null);

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', $translator->trans('authentication.account.confirmed'));
        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
