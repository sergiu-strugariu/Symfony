<?php

namespace App\Controller\Dashboard;

use App\Helper\DefaultHelper;
use DateTime;
use App\Entity\CompanyGallery;
use App\Entity\Company;
use App\Form\Type\CompanyFormType;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyController extends AbstractController
{
    #[Route('/dashboard/company/{locationType}', name: 'dashboard_company_index')]
    public function index($locationType, TranslatorInterface $translator): Response
    {
        return $this->render('dashboard/company/index.html.twig', [
            'pageTitle' => $locationType === Company::LOCATION_TYPE_CARE ? $translator->trans('controller.cares', [], 'messages') : $translator->trans('controller.providers', [], 'messages'),
            'locationType' => $locationType
        ]);
    }

    #[Route('/dashboard/company/create/{locationType}', name: 'dashboard_company_create')]
    public function create(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, LanguageHelper $languageHelper, $locationType, TranslatorInterface $translator): Response
    {
        $company = new Company();
        $form = $this->createForm(CompanyFormType::class, $company, ['language' => $languageHelper->getDefaultLanguage(), 'locationType' => $locationType]);
        $form->handleRequest($request);
        $files = DefaultHelper::COMPANY_FILE_FIELDS;

        if ($form->isSubmitted() && $form->isValid()) {
            // Generate and set unique UUID
            $company->setUuid(Uuid::v4());

            // Parse and save files
            foreach ($files as $value) {
                $file = $form->get($value)->getData();

                if (isset($file)) {
                    // Upload logo file
                    $uploadFile = $fileUploader->uploadFile(
                        $file,
                        $form,
                        $this->getParameter('app_company_path'),
                        $value
                    );

                    // Check and set @filename
                    if ($uploadFile['success']) {
                        match ($value) {
                            DefaultHelper::COMPANY_FILE_FIELDS['video'] => $company->setVideoPlaceholder($uploadFile['fileName']),
                            DefaultHelper::COMPANY_FILE_FIELDS['preview'] => $company->setFileName($uploadFile['fileName']),
                            DefaultHelper::COMPANY_FILE_FIELDS['logo'] => $company->setLogo($uploadFile['fileName'])
                        };
                    }
                }
            }

            // Save changes to DB
            $company->setUser($this->getUser());
            $company->setLocationType($locationType);
            $em->persist($company);
            $em->flush();

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_item_added', [], 'messages'));
            return $this->redirectToRoute('dashboard_company_index', ['locationType' => $locationType]);
        }


        return $this->render('dashboard/company/actions.html.twig', [
            'pageTitle' => $translator->trans('dashboard.table.add_new_item', [], 'messages'),
            'services' => json_encode(Company::getServices(), JSON_UNESCAPED_UNICODE),
            'locationType' => $locationType,
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/company/{uuid}/edit/{locationType}', name: 'dashboard_company_edit')]
    public function edit(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, LanguageHelper $languageHelper, $uuid, $locationType, TranslatorInterface $translator): Response
    {
        /**
         * Get company by @uuid
         * @var Company $company
         */
        $company = $em->getRepository(Company::class)->findOneBy(['uuid' => $uuid, 'locationType' => $locationType]);
        $files = DefaultHelper::COMPANY_FILE_FIELDS;

        if (null === $company) {
            // Set flash message
            $this->addFlash('success', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_company_index', ['locationType' => $locationType]);
        }

        // get selected language
        $locale = $request->get('locale');
        $language = $languageHelper->getLanguageByLocale($locale);


        // Get gallery images
        $galleries = $em->getRepository(CompanyGallery::class)->getGalleryByCompany($company);

        $form = $this->createForm(CompanyFormType::class, $company, ['language' => $language, 'locationType' => $locationType]);
        $form->handleRequest($request);

        // Validate form
        if ($form->isSubmitted() && $form->isValid()) {
            // Parse and save files
            foreach ($files as $value) {
                $file = $form->get($value)->getData();

                if (isset($file)) {
                    // Upload logo file
                    $uploadFile = $fileUploader->uploadFile(
                        $file,
                        $form,
                        $this->getParameter('app_company_path'),
                        $value
                    );

                    // Check and set @filename
                    if ($uploadFile['success']) {
                        match ($value) {
                            DefaultHelper::COMPANY_FILE_FIELDS['video'] => $company->setVideoPlaceholder($uploadFile['fileName']),
                            DefaultHelper::COMPANY_FILE_FIELDS['preview'] => $company->setFileName($uploadFile['fileName']),
                            DefaultHelper::COMPANY_FILE_FIELDS['logo'] => $company->setLogo($uploadFile['fileName'])
                        };
                    }
                }
            }

            // Save changes to DB
            $company->setUpdatedAt(new DateTime());
            $company->setLocationType($locationType);
            $em->persist($company);
            $em->flush();

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_edit', [], 'messages') . $company->getName());
            return $this->redirectToRoute('dashboard_company_index', ['locationType' => $locationType]);
        }

        return $this->render('dashboard/company/actions.html.twig', [
            'pageTitle' => $translator->trans('dashboard.actions.edit', [], 'messages'),
            'uuid' => $uuid,
            'galleries' => $galleries,
            'videoPlaceholder' => $company->getVideoPlaceholder(),
            'previewImage' => $company->getFileName(),
            'logo' => $company->getLogo(),
            'services' => json_encode(Company::getServices(), JSON_UNESCAPED_UNICODE),
            'locationType' => $locationType,
            'form' => $form->createView()
        ]);
    }

    #[Route('/dashboard/company/actions/{action}/{uuid}/{locationType}', name: 'dashboard_company_actions')]
    public function actions(EntityManagerInterface $em, $action, $uuid, $locationType, TranslatorInterface $translator): Response
    {
        /** @var Company $company */
        $company = $em->getRepository(Company::class)->findOneBy(['uuid' => $uuid, 'locationType' => $locationType]);

        if ($action === 'remove') {
            // Soft delete
            $company->setDeletedAt(new DateTime());
        } elseif ($action === 'moderate') {
            $company->setStatus($company->getStatus() === Company::STATUS_DRAFT ? Company::STATUS_PUBLISHED : Company::STATUS_DRAFT);
        } else {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.error_action', [], 'messages'));
            return $this->redirectToRoute('dashboard_company_index', ['locationType' => $locationType]);
        }

        if (!isset($company)) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.error_action', [], 'messages'));
            return $this->redirectToRoute('dashboard_company_index', ['locationType' => $locationType]);
        }

        // Update data
        $em->persist($company);
        $em->flush();

        // Set flash message
        $this->addFlash('success', sprintf($translator->trans('controller.success_multiple', [], 'messages'), $action === 'moderate' ? $translator->trans('controller.moderated', [], 'messages') : $translator->trans('controller.deleted', [], 'messages')));
        return $this->redirectToRoute('dashboard_company_index', ['locationType' => $locationType]);
    }
}
