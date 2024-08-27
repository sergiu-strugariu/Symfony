<?php

namespace App\Controller\Frontend;

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
use Symfony\Contracts\Translation\TranslatorInterface;

class ResettingController extends AbstractController
{
    /**
     * @throws NonUniqueResultException
     * @throws TransportExceptionInterface
     */
    #[Route('/resetting/request', name: 'app_resetting_forgot_password')]
    public function forgotPassword(Request $request, MailHelper $mail, DefaultHelper $helper, EntityManagerInterface $em, TranslatorInterface $translator): Response
    {
        // Create FormType
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);
        $recaptcha = $request->get('g-recaptcha-response');

        // Validate form
        if ($form->isSubmitted() && $form->isValid()) {
            // Validate recaptcha
            if ($helper->captchaVerify($recaptcha)) {
                $this->addFlash('error', $translator->trans('form.messages.form_recaptcha', [], 'messages'));
                return $this->redirectToRoute('app_resetting_forgot_password');
            }

            // Form data
            $formData = $form->getData();

            // Get user by @request data
            $user = $em->getRepository(User::class)->findOneBy(['email' => $formData['email'], 'enabled' => true]);

            if (!empty($user)) {
                // Generate hash by request data
                $hash = $helper->generateHash($formData['email']);
                $subject = $translator->trans('auth.forgot_password_title', [], 'messages');

                // Send email to @email
                $sent = $mail->sendMail(
                    $user->getEmail(),
                    $subject,
                    'frontend/emails/auth/reset-password.html.twig',
                    [
                        'pageTitle' => $subject,
                        'url' => $this->generateUrl('app_resetting_reset_password', [
                            'token' => $hash
                        ], UrlGeneratorInterface::ABSOLUTE_URL)
                    ]);

                if (!$sent) {
                    // Set flash message
                    $this->addFlash('error', $translator->trans('form.messages.form_details_error', [], 'messages'));
                    return $this->redirectToRoute('app_resetting_forgot_password');
                }

                // Update @user data
                $user->setPasswordRequestedAt(new \DateTime());
                $user->setConfirmationToken($hash);

                $em->persist($user);
                $em->flush();
            }

            // Set flash message
            $this->addFlash('success', $translator->trans('auth.forgot_success', [], 'messages'));
        }

        return $this->render('frontend/resetting/forgot_password.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/resetting/reset/{token}', name: 'app_resetting_reset_password')]
    public function resetPassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder, TranslatorInterface $translator, DefaultHelper $helper, $token): RedirectResponse|Response
    {
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy([
            'confirmationToken' => $token,
            'enabled' => true
        ]);

        if (isset($user) && $user->getPasswordRequestedAt() > (new \DateTime())->modify("-2 hours")) {
            // Create FormType
            $form = $this->createForm(ResetPasswordType::class);
            $form->handleRequest($request);
            $recaptcha = $request->get('g-recaptcha-response');

            // Validate form
            if ($form->isSubmitted() && $form->isValid()) {
                // Validate recaptcha
                if ($helper->captchaVerify($recaptcha)) {
                    $this->addFlash('error', $translator->trans('form.messages.form_recaptcha', [], 'messages'));
                    return $this->redirectToRoute('app_resetting_reset_password', ['token' => $token]);
                }

                $formData = $form->getData();

                // Update password
                $user->setPassword($passwordEncoder->hashPassword($user, $formData['password']));
                $user->setPasswordChangedAt(new \DateTime());

                // Reset data
                $user->setConfirmationToken(null);
                $user->setPasswordRequestedAt(null);

                // Persist and save
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', $translator->trans('auth.reset_success', [], 'messages'));
                return $this->redirectToRoute('dashboard_login');
            }

            return $this->render('frontend/resetting/reset_password.html.twig', [
                'form' => $form->createView()
            ]);
        }

        $this->addFlash('error', $translator->trans('auth.register_activate_account', [], 'messages'));
        return $this->redirectToRoute('dashboard_login');
    }
}