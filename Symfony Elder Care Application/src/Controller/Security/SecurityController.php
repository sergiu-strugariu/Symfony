<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\Type\UserRegisterFormType;
use App\Helper\DefaultHelper;
use App\Mailer\TwigMailer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Uid\Uuid;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('frontend/security/login.html.twig', [
            'class' => 'login',
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/creare-cont", name="app_register")
     */
    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder, TwigMailer $twigMailer): Response
    {
        $user = new User();

        // Create FormType
        $form = $this->createForm(UserRegisterFormType::class, $user);
        $form->handleRequest($request);

        $email = $form->get('email')->getData();
        $returnUrl = $request->get('returnUrl');

        // Validate form
        if ($form->isSubmitted() && $form->isValid()) {
            $hash = DefaultHelper::generateHash($email);

            /** @var User $getUser */
            $getUser = $em->getRepository(User::class)->findOneBy(['email' => $form->get('email')->getData()]);

            if (isset($getUser)) {
                // Set flash message
                $this->addFlash('error', 'Adresa de email introdusă este deja folosită. Vă rugăm să folosiți o altă adresă de email sau să vă autentificați cu contul existent');
                return $this->redirectToRoute('app_register', array_filter(['returnUrl' => $returnUrl]));
            }

            $plainPassword = $form['plainPassword']->getData();

            // Set password
            $hashedPassword = $passwordEncoder->hashPassword(
                $user, $plainPassword
            );

            $user->addRole('ROLE_ADMIN');
            $user->setUid(Uuid::v4());
            $user->setCreatedAt(new \DateTime());
            $user->setStatus(User::STATUS_INACTIVE);
            $user->setPassword($hashedPassword);
            $user->setConfirmationToken($hash);

            $sent = $twigMailer->sendActivatingAccountMessage(
                $user->getEmail(),
                'Activate account',
                [
                    'user' => $user,
                    'pageTitle ' => 'Activare cont',
                    'activationUrl' => $this->generateUrl('app_account_confirmation', array_filter([
                        'token' => $hash,
                        'returnUrl' => $returnUrl
                    ]), UrlGeneratorInterface::ABSOLUTE_URL)
                ]);

            if (!$sent) {
                $this->addFlash('error', 'Mesajul nu s-a putut trimite. Încearcă mai târziu.');
                return $this->redirectToRoute('app_login', array_filter(['returnUrl' => $returnUrl]));
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Felicitări! Contul dvs. a fost creat cu succes.');
            return $this->redirectToRoute('app_login');
        }


        return $this->render('frontend/security/register.html.twig', [
            'class' => 'login create-account',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/confirmare-cont/{token}", name="app_account_confirmation")
     */
    public function confirm(Request $request, EntityManagerInterface $em, $token): Response
    {
        $returnUrl = $request->get('returnUrl');

        $user = $em->getRepository(User::class)->findOneBy([
            'confirmationToken' => $token
        ]);

        if ($user) {
            $user->setStatus(User::STATUS_ACTIVE);
            $user->setConfirmationToken(null);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Adresa de email a fost confirmata, te poți loga acum.');
            return $this->redirectToRoute('app_login', array_filter(['returnUrl' => $returnUrl]));
        }

        $this->addFlash('error', 'Adresa de email a fost deja verificata, te poți loga.');
        return $this->redirectToRoute('app_login', array_filter(['returnUrl' => $returnUrl]));
    }

    /**
     * @Route("/logout", name="dashboard_logout", methods={"GET"})
     * @throws Exception
     */
    public function logout(): void
    {
        // controller can be blank: it will never be called!
        throw new Exception('Don\'t forget to activate logout in security.yaml');
    }
}
