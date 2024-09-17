<?php

namespace App\Controller\Frontend;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Helper\DefaultHelper;
use App\Helper\MailHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Uid\Uuid;

class SecurityController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailHelper $mail, DefaultHelper $helper): Response
    {
        $user = $this->getUser();
        if ($user) {
            $this->addFlash('information', 'You are logged in.');
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setUuid(Uuid::v4());

            $sent = $mail->sendMail(
                $user->getEmail(),
                'Activate account',
                'emails/mail.html.twig', [
                    'title' => 'Reset Password',
                    'user' => $user,
                    'url' => $this->generateUrl('app_account_confirmation', [
                        'uuid' => $user->getUuid()
                    ], UrlGeneratorInterface::ABSOLUTE_URL)
                ]
            );

            if (!$sent) {
                $this->addFlash('error', 'An error occurred please try again later.');
                return $this->redirectToRoute('app_register');
            }

            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'The account has been created successfully.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('frontend/auth/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $user = $this->getUser();
        if ($user) {
            $this->addFlash('information', 'You are logged in.');
            return $this->redirectToRoute('app_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('frontend/auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/resetting/request', name: 'app_resetting_forgot_password')]
    public function forgotPassword(Request $request, MailHelper $mail, DefaultHelper $helper, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formData = $form->getData();

            $user = $em->getRepository(User::class)->findOneBy(['email' => $formData['email']]);

            if (null === $user) {
                $this->addFlash('error', 'This account does not exists.');
                return $this->redirectToRoute("app_resetting_forgot_password");
            }

            if (!$user->isEnabled()) {
                $this->addFlash('error', 'This account is not verified.');
                return $this->redirectToRoute("app_login");
            }

            $hash = $helper->generateHash($user->getEmail());

            $sent = $mail->sendMail(
                $user->getEmail(),
                'Reset password',
                'emails/mail.html.twig', [
                    'title' => 'Reset Password',
                    'user' => $user,
                    'url' => $this->generateUrl('app_resetting_reset_password', [
                        'token' => $hash
                    ], UrlGeneratorInterface::ABSOLUTE_URL)
                ]
            );

            if (!$sent) {
                $this->addFlash('error', 'An error occurred please try again later.');
                return $this->redirectToRoute('app_resetting_forgot_password');
            }

            $user->setPasswordRequestedAt(new \DateTime());
            $user->setConfirmationToken($hash);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'The message was sent successfully');
            $this->redirectToRoute("app_login");
        }

        return $this->render('frontend/auth/forgot-password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/resetting/reset/{token}', name: 'app_resetting_reset_password')]
    public function resetPassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder, $token): RedirectResponse|Response
    {
        $user = $em->getRepository(User::class)->findOneBy([
            'confirmationToken' => $token,
            'enabled' => true
        ]);

        if ($user) {
            $form = $this->createForm(ResetPasswordType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();

                $user->setPassword($passwordEncoder->hashPassword(
                    $user,
                    $formData['password']
                ));

                $user->setConfirmationToken(null);
                $user->setPasswordRequestedAt(null);

                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'The account has been updated successfully');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('frontend/auth/reset-password.html.twig', [
                'form' => $form->createView()
            ]);
        }

        $this->addFlash('danger', 'This token has expired');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/confirm/account/{uuid}', name: 'app_account_confirmation')]
    public function confirm(EntityManagerInterface $em, $uuid): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['uuid' => $uuid]);

        if (!$user) {
            $this->addFlash('error', "This user does not exists.");
            return $this->redirectToRoute('app_login');
        }

        if ($user->isEnabled()) {
            $this->addFlash('success', "This account was already activated.");
            return $this->redirectToRoute('app_login');
        }

        $user->setEnabled(true);
        $user->setConfirmationToken(null);

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', "The account was activated with success.");
        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
