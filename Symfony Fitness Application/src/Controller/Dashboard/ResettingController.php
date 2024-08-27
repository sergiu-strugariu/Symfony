<?php

namespace App\Controller\Dashboard;

use App\Entity\User;
use App\Form\Type\ResetPasswordType;
use App\Form\Type\ForgotPasswordType;
use App\Helper\DefaultHelper;
use App\Helper\MailHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ResettingController extends AbstractController
{
    /**
     * @throws NonUniqueResultException
     * @throws TransportExceptionInterface
     */
    #[Route('/dashboard/resetting/request', name: 'dashboard_resetting_forgot_password')]
    public function forgotPassword(Request $request, MailHelper $mail, DefaultHelper $helper, EntityManagerInterface $em): Response
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

            if (!$user->isEnabled()) {
                $this->addFlash('error', 'This account is not activated.');
                $this->redirectToRoute("dashboard_login");
            }

            // Generate hash by request data
            $hash = $helper->generateHash($formData['email']);

            $sent = $mail->sendMail(
                $user->getEmail(),
                'Reset password',
                'dashboard/emails/resetting.html.twig',
                [
                    'user' => $user,
                    'resettingUrl' => $this->generateUrl('dashboard_resetting_reset_password', [
                        'token' => $hash
                    ], UrlGeneratorInterface::ABSOLUTE_URL)
                ]);

            if (!$sent) {
                // Set flash message
                $this->addFlash('danger',"We've encountered an unexpected error, please try again later");

                return $this->redirectToRoute('dashboard_resetting_forgot_password');
            }

            // Update @user data
            $user->setPasswordRequestedAt(new \DateTime());
            $user->setConfirmationToken($hash);

            $em->persist($user);
            $em->flush();

            // Set flash message
            $this->addFlash('success', 'A link to reset your password has been sent to your email address');
        }

        return $this->render('dashboard/resetting/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/dashboard/resetting/reset/{token}', name: 'dashboard_resetting_reset_password')]
    public function resetPassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder, $token): RedirectResponse|Response
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

                $this->addFlash('success', 'Congratulations! You have successfully updated your password');
                return $this->redirectToRoute('dashboard_login');
            }

            return $this->render('dashboard/resetting/reset_password.html.twig', [
                'form' => $form->createView()
            ]);
        }

        $this->addFlash('danger', 'This token has expired');
        return $this->redirectToRoute('dashboard_login');
    }
}