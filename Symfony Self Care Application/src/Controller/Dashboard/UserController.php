<?php

namespace App\Controller\Dashboard;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Helper\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    #[Route('/dashboard/user', name: 'dashboard_user_index')]
    public function index(): Response
    {
        return $this->render('dashboard/user/index.html.twig', []);
    }

    #[Route('/dashboard/user/{uuid}/edit', name: 'dashboard_user_edit')]
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder, TranslatorInterface $translator, FileUploader $fileUploader, $uuid): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['uuid' => $uuid]);

        if (null === $user) {
            return $this->redirectToRoute('dashboard_user_index');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Validate form
        if ($form->isSubmitted() && $form->isValid()) {
            // Get data from the form
            $file = $form->get('profilePicture')->getData();

            // Update password
            $plainPassword = $form->get('plainPassword')->getData();

            if (!empty($plainPassword)) {
                $user->setPassword($passwordEncoder->hashPassword(
                    $user,
                    $plainPassword
                ));
            }

            // Check uploaded file
            if (isset($file)) {
                // Upload company file
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_user_path'),
                    'profilePicture'
                );

                // Check and set @filename
                if ($uploadFile['success']) {
                    $user->setProfilePicture($uploadFile['fileName']);
                }
            }

            // save changes to DB
            $em->persist($user);
            $em->flush();

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_edit', [], 'messages'));

            return $this->redirectToRoute('dashboard_user_index');
        }

        return $this->render('dashboard/user/edit.html.twig', [
            'form' => $form->createView(),
            'fileName' => $user->getProfilePicture()
        ]);
    }

    #[Route('/dashboard/user/{uuid}/delete', name: 'dashboard_user_delete')]
    public function delete(EntityManagerInterface $em, TranslatorInterface $translator, $uuid): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['uuid' => $uuid]);

        if (null === $user) {
            return $this->redirectToRoute('dashboard_user_index');
        }

        // Soft delete
        $user->setDeletedAt(new \DateTime());

        // save changes to DB
        $em->persist($user);
        $em->flush();

        // Set flash message
        $this->addFlash('success', $translator->trans('controller.success_delete', [], 'messages'));

        return $this->redirectToRoute('dashboard_user_index');
    }
}
