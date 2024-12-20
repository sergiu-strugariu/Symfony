<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\Type\ForgotPasswordType;
use App\Form\Type\ResetPasswordType;
use App\Helper\TokenGenerator;
use App\Mailer\TwigMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResettingController extends AbstractController
{

    /**
     * @Route("/resetting/request", name="dashboard_resetting_forgot_password")
     */
    public function forgotPassword(Request $request, TwigMailer $twigMailer, TokenGenerator $tokenGenerator, EntityManagerInterface $em)
    {
        // Create FormType
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        // Validate form
        if ($form->isSubmitted() && $form->isValid()) {
            // Form data
            $formData = $form->getData();

            // Get user by @request data
            $user = $em->getRepository(User::class)->findOneBy(['email' => $formData['email']]);

            if (null !== $user) {
                // Generate hash by request data
                $hash = $tokenGenerator->generateToken();

                $sent = $twigMailer->sendForgotPasswordMessage(
                    $user->getEmail(),
                    'Reset password',
                    [
                        'user' => $user,
                        'pageTitle ' => 'Resetare parola',
                        'resettingUrl' => $this->generateUrl('dashboard_resetting_reset_password', [
                            'token' => $hash
                        ], UrlGeneratorInterface::ABSOLUTE_URL)
                    ]);

                if (!$sent) {
                    // Set flash message
                    $this->addFlash('primary', "Am întâmpinat o eroare neașteptată, vă rugăm să încercați din nou mai târziu");
                    return $this->redirectToRoute('dashboard_resetting_forgot_password');
                }

                // Update @user data
                $user->setPasswordRequestedAt(new \DateTime());
                $user->setConfirmationToken($hash);

                $em->persist($user);
                $em->flush();

                // Set flash message
                $this->addFlash('success', 'Un link pentru a vă reseta parola a fost trimis la adresa dvs. de e-mail');
                return $this->render('frontend/security/resetting/email-sent.html.twig', [
                    'class' => 'login email-sent'
                ]);
            }
        }

        return $this->render('frontend/security/resetting/forgot_password.html.twig', [
            'class' => 'login password-request',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/resetting/reset/{token}", name="dashboard_resetting_reset_password")
     */
    public function resetPassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder, $token)
    {
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy([
            'confirmationToken' => $token
        ]);

        if ($user) {
            // Create FormType
            $form = $this->createForm(ResetPasswordType::class);
            $form->handleRequest($request);

            // Validate form
            if ($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();

                // Update password
                $user->setPassword($passwordEncoder->hashPassword(
                    $user,
                    $formData['password']
                ));

                $user->setConfirmationToken(null);
                $user->setPasswordRequestedAt(null);

                $em->persist($user);
                $em->flush();

                $this->addFlash('primary', 'Felicitări! V-ați actualizat cu succes parola');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('frontend/security/resetting/reset_password.html.twig', [
                'class' => 'login password-request',
                'form' => $form->createView()
            ]);
        }

        $this->addFlash('error', 'Acest token a expirat');
        return $this->redirectToRoute('app_login');
    }
}