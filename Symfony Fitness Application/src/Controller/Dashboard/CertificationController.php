<?php

namespace App\Controller\Dashboard;

use App\Entity\Certification;
use App\Entity\CertificationTranslation;
use App\Form\Type\CertificationType;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class CertificationController extends AbstractController
{
    #[Route('/dashboard/certifications', name: 'dashboard_certifications_index')]
    public function index(): Response
    {
        return $this->render('dashboard/certification/index.html.twig');
    }

    #[Route('/dashboard/certificate/create', name: 'dashboard_certificate_create')]
    public function create(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader): Response
    {
        $languages = $languageHelper->getAllLanguages();

        $certificate = new Certification();
        $form = $this->createForm(CertificationType::class, $certificate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $certificate->setUuid(Uuid::v4());
            $file = $form->get('image')->getData();

            foreach ($languages as $language) {
                $certificateTranslation = new CertificationTranslation();
                $certificateTranslation->setLanguage($language);
                $certificateTranslation->setCertification($certificate);
                $certificateTranslation->setTitle($form->get('title')->getData());
                $certificateTranslation->setDescription($form->get('description')->getData());
                $certificateTranslation->setLevel($form->get('level')->getData() ?? '');

                $em->persist($certificateTranslation);
            }

            $uploadFile = $fileUploader->uploadFile(
                $file,
                $form,
                $this->getParameter('app_certificate_path')
            );

            if ($uploadFile['success']) {
                $certificate->setImageName($uploadFile['fileName']);
            }

            $em->persist($certificate);
            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully added a new certificate.');
            return $this->redirectToRoute('dashboard_certifications_index');
        }

        return $this->render('dashboard/certification/management.html.twig', [
            'form' => $form->createView(),
            'editMode' => false,
        ]);
    }

    #[Route('/dashboard/certificate/{uuid}/edit', name: 'dashboard_certificate_edit')]
    public function edit(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, $uuid): Response
    {
        $certificate = $em->getRepository(Certification::class)->findOneBy(['uuid' => $uuid]);
        if (null === $certificate) return $this->redirectToRoute('dashboard_index');

        $locale = $request->get('locale', $this->getParameter('default_locale'));
        $language = $languageHelper->getLanguageByLocale($locale);

        $certificateTranslation = $em->getRepository(CertificationTranslation::class)->findOneBy([
            'certification' => $certificate,
            'language' => $language
        ]);

        if (null === $certificateTranslation) {
            $certificateTranslation = new CertificationTranslation();
            $certificateTranslation->setCertification($certificate);
            $certificateTranslation->setLanguage($language);
        }

        $form = $this->createForm(CertificationType::class, $certificate, [
            'translation' => $certificateTranslation
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();

            $certificateTranslation->setTitle($form->get('title')->getData());
            $certificateTranslation->setDescription($form->get('description')->getData());
            $certificateTranslation->setLevel($form->get('level')->getData() ?? "");

            $uploadFile = $fileUploader->uploadFile(
                $file,
                $form,
                $this->getParameter('app_certificate_path')
            );

            if ($uploadFile['success']) {
                $certificate->setImageName($uploadFile['fileName']);
            }

            $em->persist($certificateTranslation);
            $em->persist($certificate);
            $em->flush();

            $this->addFlash('success', 'You have successfully edited the certificate.');
            return $this->redirectToRoute('dashboard_certifications_index');
        }

        return $this->render('dashboard/certification/management.html.twig', [
            'form' => $form->createView(),
            'entity' => $certificate,
            'editMode' => true
        ]);
    }

    #[Route('/dashboard/certificate/{uuid}/delete', name: 'dashboard_certificate_delete')]
    public function delete(EntityManagerInterface $em, $uuid): Response
    {
        $certificate = $em->getRepository(Certification::class)->findOneBy(['uuid' => $uuid]);
        if (null === $certificate) return $this->redirectToRoute('dashboard_index');

        $certificate->setDeletedAt(new \DateTime());
        $em->persist($certificate);
        $em->flush();

        $this->addFlash('success', 'The certificate has been successfully deleted');
        return $this->redirectToRoute('dashboard_certifications_index');
    }
}
