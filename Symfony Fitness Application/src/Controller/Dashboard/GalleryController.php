<?php

namespace App\Controller\Dashboard;

use App\Entity\Gallery;
use App\Form\Type\GalleryType;
use App\Helper\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class GalleryController extends AbstractController
{
    #[Route('/dashboard/galleries', name: 'dashboard_gallery_index')]
    public function index(): Response
    {
        return $this->render('dashboard/gallery/index.html.twig');
    }

    #[Route('/dashboard/gallery/create', name: 'dashboard_gallery_create')]
    public function create(Request $request, EntityManagerInterface $em, FileUploader $fileUploader): Response
    {
        $gallery = new Gallery();

        $form = $this->createForm(GalleryType::class, $gallery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gallery->setUuid(Uuid::v4());
            $file = $form->get('image')->getData();

            $uploadFile = $fileUploader->uploadFile(
                $file,
                $form,
                $this->getParameter('app_gallery_path')
            );

            if ($uploadFile['success']) {
                $gallery->setFeaturedImageName($uploadFile['fileName']);
            }

            $em->persist($gallery);
            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully added a new gallery.');
            return $this->redirectToRoute('dashboard_gallery_index');
        }

        return $this->render('dashboard/gallery/management.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/gallery/{uuid}/edit', name: 'dashboard_gallery_edit')]
    public function edit(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, $uuid): Response
    {
        $gallery = $em->getRepository(Gallery::class)->findOneBy(['uuid' => $uuid]);
        if (null === $gallery) {
            return $this->redirectToRoute('dashboard_gallery_index');
        }

        $form = $this->createForm(GalleryType::class, $gallery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();

            $uploadFile = $fileUploader->uploadFile(
                $file,
                $form,
                $this->getParameter('app_gallery_path')
            );

            if ($uploadFile['success']) {
                $gallery->setFeaturedImageName($uploadFile['fileName']);
            }
            
            $em->persist($gallery);
            $em->flush();

            $this->addFlash('success', 'You have successfully edited the gallery');
            return $this->redirectToRoute('dashboard_gallery_index');
        }

        return $this->render('dashboard/gallery/management.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/gallery/{uuid}/delete', name: 'dashboard_gallery_delete')]
    public function delete(EntityManagerInterface $em, $uuid): Response
    {
        $gallery = $em->getRepository(Gallery::class)->findOneBy(['uuid' => $uuid]);
        if (null === $gallery) {
            return $this->redirectToRoute('dashboard_gallery_index');
        }

        $em->remove($gallery);
        $em->flush();

        $this->addFlash('success', 'The gallery has been successfully deleted');
        return $this->redirectToRoute('dashboard_gallery_index');
    }
}
