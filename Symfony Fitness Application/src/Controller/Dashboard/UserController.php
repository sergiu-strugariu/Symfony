<?php

namespace App\Controller\Dashboard;

use App\Entity\User;
use App\Form\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/dashboard/users', name: 'dashboard_user_index')]
    public function index(): Response
    {
        return $this->render('dashboard/user/index.html.twig', []);
    }
    
    #[Route('/dashboard/user/{uuid}/edit', name: 'dashboard_user_edit')]
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordEncoder, $uuid): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['uuid' => $uuid]);
        if (null === $user) {
            return $this->redirectToRoute('dashboard_user_index');
        }
        
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Validate form
        if ($form->isSubmitted() && $form->isValid()) {
            // Update password
            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword($passwordEncoder->hashPassword(
                    $user,
                    $plainPassword
                ));
            }
            
            // save changes to DB
            $em->persist($user);
            $em->flush();

            // Set flash message
            $this->addFlash('success', 'You have successfully edited the user');
            
            return $this->redirectToRoute('dashboard_user_index');
        } 
        
        return $this->render('dashboard/user/edit.html.twig', [
             'form' => $form->createView()
        ]);
    }
    
    #[Route('/dashboard/user/{uuid}/delete', name: 'dashboard_user_delete')]
    public function delete(EntityManagerInterface $em, $uuid): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['uuid' => $uuid]);
        if (null === $user) {
            return $this->redirectToRoute('dashboard_user_index');
        }
        
        // soft delete
        $user->setDeletedAt(new \DateTime());

        // save changes to DB
        $em->persist($user);
        $em->flush();
        
        // Set flash message
        $this->addFlash('success', 'You have successfully deleted the user');
        
        return $this->redirectToRoute('dashboard_user_index');
    }
}
