<?php

namespace App\Controller\Dashboard;

use DateTime;
use Exception;
use App\Entity\MembershipPackage;
use App\Entity\MembershipPackageTranslation;
use App\Form\Type\MembershipPackageForm;
use App\Helper\DefaultHelper;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MembershipPackageController extends AbstractController
{
    #[Route('/dashboard/secure/membership-packages', name: 'dashboard_membership_index')]
    public function index(): Response
    {
        return $this->render('dashboard/package/index.html.twig');
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/secure/membership-package/create', name: 'dashboard_membership_create')]
    public function create(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, TranslatorInterface $translator): Response
    {
        // get default language
        $language = $languageHelper->getDefaultLanguage();

        $package = new MembershipPackage();
        $form = $this->createForm(MembershipPackageForm::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // get data from the form
            $file = $form->get('fileName')->getData();

            // create translation and set data
            $packageTranslation = new MembershipPackageTranslation();
            $packageTranslation->setMembershipPackage($package);
            $packageTranslation->setLanguage($language);
            $packageTranslation->setName($form->get('name')->getData());
            $packageTranslation->setDescription($form->get('description')->getData());

            $package->addMembershipPackageTranslation($packageTranslation);

            // Upload file
            $uploadFile = $fileUploader->uploadFile($file, $form, $this->getParameter('app_membership_package_path'));

            // Check and set @filename
            if ($uploadFile['success']) {
                // save new item to DB
                $package->setFileName($uploadFile['fileName']);

                $em->persist($package);
                $em->persist($packageTranslation);
                $em->flush();

                // Set flash message
                $this->addFlash('success', $translator->trans('controller.success_item_added', [], 'messages'));
                return $this->redirectToRoute('dashboard_membership_index');
            }
        }

        return $this->render('dashboard/package/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.create_membership', [], 'messages')
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/secure/membership-package/{uuid}/edit', name: 'dashboard_membership_edit')]
    public function edit(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, $uuid, TranslatorInterface $translator): Response
    {
        $filePath = $this->getParameter('app_membership_package_path');
        $fileRemoved = true;

        /** @var MembershipPackage $package */
        $package = $em->getRepository(MembershipPackage::class)->findOneBy(['uuid' => $uuid]);

        if (empty($package)) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_membership_index');
        }

        // get selected language
        $locale = $request->get('locale');
        $language = $languageHelper->getLanguageByLocale($locale);

        // get translation
        $packageTranslation = $em->getRepository(MembershipPackageTranslation::class)->findOneBy([
            'membershipPackage' => $package,
            'language' => $language
        ]);

        $packageTranslation = $packageTranslation ?? new MembershipPackageTranslation();

        // Init form & handle request data
        $form = $this->createForm(MembershipPackageForm::class, $package, ['translation' => $packageTranslation]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('fileName')->getData();

            $packageTranslation->setName($form->get('name')->getData());
            $packageTranslation->setDescription($form->get('description')->getData());

            $packageTranslation->setMembershipPackage($package);
            $packageTranslation->setLanguage($language);

            // Check exist new file
            if (isset($file)) {
                $fileRemoved = false;

                // Upload file
                $uploadFile = $fileUploader->uploadFile($file, $form, $filePath);

                // Check uploaded status
                if ($uploadFile['success']) {
                    // Remove file from storage
                    $fileRemoved = $fileUploader->removeFile($filePath, $package->getFileName());

                    // Set new fileName
                    $package->setFileName($uploadFile['fileName']);
                }
            }

            if ($fileRemoved) {
                // save changes to DB
                $em->persist($package);
                $em->persist($packageTranslation);
                $em->flush();

                // Set flash message
                $this->addFlash('success', $translator->trans('controller.success_edit', [], 'messages'));
                return $this->redirectToRoute('dashboard_membership_index');
            }
        }

        return $this->render('dashboard/package/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.edit_membership', [], 'messages'),
            'uuid' => $package->getUuid(),
            'fileName' => $package->getFileName()
        ]);
    }

    #[Route('/dashboard/secure/membership-package/actions/{action}/{uuid}', name: 'dashboard_membership_actions')]
    public function actions(EntityManagerInterface $em, $action, $uuid, TranslatorInterface $translator): Response
    {
        /** @var MembershipPackage $package */
        $package = $em->getRepository(MembershipPackage::class)->findOneBy(['uuid' => $uuid]);

        if (empty($package)) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_membership_index');
        }

        switch ($action) {
            case 'remove':
                // Soft delete
                $package->setDeletedAt(new DateTime());
                break;
            case 'moderate':
                // Update status
                $package->setStatus($package->getStatus() === DefaultHelper::STATUS_DRAFT ? DefaultHelper::STATUS_PUBLISHED : DefaultHelper::STATUS_DRAFT);
                break;
            default:
                // Set flash message and redirect
                $this->addFlash('danger', $translator->trans('controller.error_action', [], 'messages'));
                return $this->redirectToRoute('dashboard_membership_index');
        }

        // Update data
        $em->persist($package);
        $em->flush();

        // Set flash message
        $this->addFlash('success', sprintf($translator->trans('controller.success_multiple', [], 'messages'), $action === 'moderate' ? $translator->trans('controller.moderated', [], 'messages') : $translator->trans('controller.deleted', [], 'messages')));
        return $this->redirectToRoute('dashboard_membership_index');
    }
}