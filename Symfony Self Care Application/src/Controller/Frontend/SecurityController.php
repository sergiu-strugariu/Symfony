<?php

namespace App\Controller\Frontend;

use App\Entity\User;
use App\Form\Type\RegisterFormType;
use App\Helper\DefaultHelper;
use App\Helper\FileUploader;
use App\Helper\MailHelper;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecurityController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/creare-cont/{type}', name: 'app_register_form')]
    public function register(EntityManagerInterface $em, Request $request, FileUploader $fileUploader, UserPasswordHasherInterface $passwordEncoder, TranslatorInterface $translator, MailHelper $mail, DefaultHelper $helper, $type): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterFormType::class, $user);
        $form->handleRequest($request);
        $email = $form->get('email')->getData();
        $recaptcha = $request->get('g-recaptcha-response');
        $returnUrl = $request->get('returnUrl');

        // Validate type
        if (!in_array($type, $user::getUserTypes())) {
            return $this->redirectToRoute('app_create_account', array_filter(['returnUrl' => $returnUrl]));
        }

        /** @var User $getUser */
        $getUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (isset($getUser)) {
            // Set flash message
            $this->addFlash('error', $translator->trans('auth.register_double_message', [], 'messages'));

            return $this->redirectToRoute('app_register_form', array_filter([
                'type' => $type,
                'returnUrl' => $returnUrl
            ]));
        }

        // Form validate
        if ($form->isSubmitted() && $form->isValid()) {
            $hash = DefaultHelper::generateHash($email);

            // Validate recaptcha
            if ($helper->captchaVerify($recaptcha)) {
                $this->addFlash('error', $translator->trans('form.messages.form_recaptcha', [], 'messages'));

                return $this->redirectToRoute('app_register_form', array_filter([
                    'type' => $type,
                    'returnUrl' => $returnUrl
                ]));
            }

            // get data from the form
            $file = $form->get('profilePicture')->getData();

            // Check exist and upload file
            if (!empty($file)) {
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_user_path'),
                    'profilePicture'
                );

                if ($uploadFile['success']) {
                    $user->setProfilePicture($uploadFile['fileName']);
                }
            }

            // Hash and set user password
            $user->setUuid(Uuid::v4());
            $user->setRole($type === User::USER_COMPANY ? User::ROLE_COMPANY : User::ROLE_CLIENT);
            $user->setPassword($passwordEncoder->hashPassword($user, $form->get('plainPassword')->getData()));
            $user->setConfirmationToken($hash);

            // Persist and save data
            $em->persist($user);

            // Send email to @user
            $subject = $translator->trans('auth.register_subject_mail', [], 'messages') . ' - ' . $this->getParameter('app_name');
            $emailSend = $mail->sendMail(
                $email,
                $subject,
                'frontend/emails/auth/confirm-email.html.twig',
                [
                    'pageTitle' => $subject,
                    'url' => $this->generateUrl('app_register_confirm', array_filter([
                        'token' => $hash,
                        'returnUrl' => $returnUrl
                    ]), UrlGeneratorInterface::ABSOLUTE_URL)
                ]
            );

            // Check send email and create user
            if ($emailSend) $em->flush();

            // Set flash message
            $message = $emailSend ? 'auth.register_success_message' : 'form.messages.form_details_error';
            $this->addFlash($emailSend ? 'success' : 'error', $translator->trans($message, [], 'messages'));

            return $this->redirectToRoute('app_register_form', array_filter([
                'type' => $type,
                'returnUrl' => $returnUrl
            ]));
        }

        return $this->render('frontend/security/register.html.twig', [
            'form' => $form->createView(),
            'type' => $type
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route(path: '/confirmare-email/{token}', name: 'app_register_confirm')]
    public function confirmEmail(Request $request, EntityManagerInterface $em, TranslatorInterface $translator, Security $security, MailHelper $mail, $token): Response
    {
        $returnUrl = $request->get('returnUrl');

        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy([
            'confirmationToken' => $token,
            'enabled' => false
        ]);

        if (isset($user)) {
            // Send email to @user
            $emailSend = $mail->sendMail(
                $user->getEmail(),
                $this->getParameter('app_name'),
                'frontend/emails/auth/welcome.html.twig',
                ['pageTitle' => $this->getParameter('app_name')]
            );

            // Check email send
            if ($emailSend) {
                // Enable user account
                $user->setEnabled(true);
                $user->setConfirmationToken(null);
                // Persist and save
                $em->persist($user);
                $em->flush();

                // Login user automatic
                $security->login($user, 'form_login');

                // Get path by params
                $targetPath = empty($returnUrl) ? $this->generateUrl('dashboard_my_account') : $returnUrl;

                // Redirect
                $this->addFlash('success', $translator->trans('auth.confirm_email_success', [], 'messages'));
                return $this->redirect($request->request->get('_target_path', $targetPath));
            }
        }

        $this->addFlash('error', $translator->trans('auth.register_activate_account', [], 'messages'));
        return $this->redirectToRoute('dashboard_login');
    }

    #[Route(path: '/login', name: 'dashboard_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('frontend/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'dashboard_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
