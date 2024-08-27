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

        // Validate form
        if ($form->isSubmitted() && $form->isValid()) {

            // Form data
            $formData = $form->getData();

            // Get user by @request data
            $user = $em->getRepository(User::class)->findOneBy(['email' => $formData['email']]);

            if (null === $user) {
                $this->addFlash('error', $translator->trans('authentication.account.not_exists'));
                return $this->redirectToRoute("app_resetting_forgot_password");
            }

            if (null !== $user) {
                if (!$user->isEnabled()) {
                    $this->addFlash('error', $translator->trans('authentication.account.not_verified'));
                    return $this->redirectToRoute("app_login");
                }

                // Generate hash by request data
                $hash = $helper->generateHash($user->getEmail());

                $sent = $mail->sendMail(
                    $user->getEmail(),
                    'Reset password',
                    'frontend/emails/resetting.html.twig', [
                        'title' => $translator->trans('mails.resetting.title'),
                        'user' => $user,
                        'resettingUrl' => $this->generateUrl('app_resetting_reset_password', [
                            'token' => $hash
                        ], UrlGeneratorInterface::ABSOLUTE_URL)
                    ]
                );

                if (!$sent) {
                    // Set flash message
                    $this->addFlash('danger', $translator->trans('authentication.account.default_password_error'));

                    return $this->redirectToRoute('app_resetting_forgot_password');
                }

                // Update @user data
                $user->setPasswordRequestedAt(new \DateTime());
                $user->setConfirmationToken($hash);

                $em->persist($user);
                $em->flush();

                // Set flash message
                $this->addFlash('success', $translator->trans('authentication.mail.success'));
                $this->redirectToRoute("app_login");
            }
            
        }

        return $this->render('frontend/default/forgot-password.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/resetting/reset/{token}', name: 'app_resetting_reset_password')]
    public function resetPassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder, TranslatorInterface $translator, $token): RedirectResponse|Response
    {
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy([
            'confirmationToken' => $token,
            'enabled' => true
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

                $this->addFlash('success', $translator->trans('authentication.account.success'));
                return $this->redirectToRoute('app_login');
            }

            return $this->render('frontend/default/reset-password.html.twig', [
                'form' => $form->createView()
            ]);
        }

        $this->addFlash('danger', 'This token has expired');
        return $this->redirectToRoute('app_login');
    }
}