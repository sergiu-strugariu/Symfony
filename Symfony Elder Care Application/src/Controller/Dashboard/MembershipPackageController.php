<?php

namespace App\Controller\Dashboard;

use App\Helper\MembershipHelper;
use DateTime;
use Exception;
use App\Entity\MembershipPackage;
use App\Form\MembershipPackageFormType;
use App\Helper\DefaultHelper;
use App\Helper\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MembershipPackageController extends AbstractController
{
    /**
     * @Route("/dashboard/secure/membership-packages", name="dashboard_membership_index")
     */
    public function index(): Response
    {
        return $this->render('dashboard/package/index.html.twig');
    }

    /**
     * @throws Exception
     * @Route("/dashboard/secure/membership-package/create", name="dashboard_membership_create")
     */
    public function create(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, MembershipHelper $membershipHelper): Response
    {
        $package = new MembershipPackage();
        $modules = MembershipPackage::MODULES;

        $form = $this->createForm(MembershipPackageFormType::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // get data from the form
            $file = $form->get('fileName')->getData();

            // parse checkboxes
            $modules = $membershipHelper->parseCheckboxResponse($request->request->all());

            // Upload file
            $uploadFile = $fileUploader->uploadFile($file, $form, $this->getParameter('app_membership_package_path'));

            // Check and set @filename
            if ($uploadFile['success']) {
                // save new item to DB
                $package->setFileName($uploadFile['fileName']);

                $package->setAdministrativeModule($modules[MembershipPackage::MODULE_ADMINISTRATIVE]);
                $package->setMedicalModule($modules[MembershipPackage::MODULE_MEDICAL]);
                $package->setPhysiotherapyModule($modules[MembershipPackage::MODULE_PHYSIOTHERAPY]);
                $package->setInfirmaryModule($modules[MembershipPackage::MODULE_INFIRMARY]);
                $package->setReceptionModule($modules[MembershipPackage::MODULE_RECEPTION]);
                $package->setKitchenModule($modules[MembershipPackage::MODULE_KITCHEN]);

                $em->persist($package);
                $em->flush();

                // Set flash message
                $this->addFlash('success', 'Felicitări, ați adăugat cu succes un element nou.');
                return $this->redirectToRoute('dashboard_membership_index');
            }
        }

        return $this->render('dashboard/package/actions.html.twig', [
            'form' => $form->createView(),
            'modules' => $modules,
            'pageTitle' => 'Creați pachet'
        ]);
    }

    /**
     * @throws Exception
     * @Route("/dashboard/secure/membership-package/{uuid}/edit", name="dashboard_membership_edit")
     */
    public function edit(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, MembershipHelper $membershipHelper, $uuid): Response
    {
        /** @var MembershipPackage $package */
        $package = $em->getRepository(MembershipPackage::class)->findOneBy(['uuid' => $uuid]);

        if (empty($package)) {
            // Set flash message
            $this->addFlash('danger', 'Acest conținut nu există.');
            return $this->redirectToRoute('dashboard_membership_index');
        }

        $modules = [
            $package::MODULE_ADMINISTRATIVE => $package->getAdministrativeModule(),
            $package::MODULE_MEDICAL => $package->getMedicalModule(),
            $package::MODULE_PHYSIOTHERAPY => $package->getPhysiotherapyModule(),
            $package::MODULE_INFIRMARY => $package->getInfirmaryModule(),
            $package::MODULE_RECEPTION => $package->getReceptionModule(),
            $package::MODULE_KITCHEN => $package->getKitchenModule()
        ];

        // Init form & handle request data
        $form = $this->createForm(MembershipPackageFormType::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filePath = $this->getParameter('app_membership_package_path');
            $file = $form->get('fileName')->getData();

            // Parse checkboxes
            $modules = $membershipHelper->parseCheckboxResponse($request->request->all());

            // Check exist new file
            if (isset($file)) {
                // Upload file
                $uploadFile = $fileUploader->uploadFile($file, $form, $filePath);

                // Check uploaded status
                if ($uploadFile['success']) {
                    // Remove file from storage
                    $fileUploader->removeFile($filePath, $package->getFileName());

                    // Set new fileName
                    $package->setFileName($uploadFile['fileName']);
                }
            }

            // Update modules
            $package->setAdministrativeModule($modules[MembershipPackage::MODULE_ADMINISTRATIVE]);
            $package->setMedicalModule($modules[MembershipPackage::MODULE_MEDICAL]);
            $package->setPhysiotherapyModule($modules[MembershipPackage::MODULE_PHYSIOTHERAPY]);
            $package->setInfirmaryModule($modules[MembershipPackage::MODULE_INFIRMARY]);
            $package->setReceptionModule($modules[MembershipPackage::MODULE_RECEPTION]);
            $package->setKitchenModule($modules[MembershipPackage::MODULE_KITCHEN]);

            // save changes to DB
            $em->persist($package);
            $em->flush();

            // Set flash message
            $this->addFlash('success', 'Ați editat cu succes acest conținut.');
            return $this->redirectToRoute('dashboard_membership_index');
        }

        return $this->render('dashboard/package/actions.html.twig', [
            'form' => $form->createView(),
            'fileName' => $package->getFileName(),
            'modules' => $modules,
            'pageTitle' => 'Editare pachet'
        ]);
    }

    /**
     * @Route("/dashboard/secure/membership-package/actions/{action}/{uuid}", name="dashboard_membership_actions")
     */
    public function actions(EntityManagerInterface $em, $action, $uuid): Response
    {
        /** @var MembershipPackage $package */
        $package = $em->getRepository(MembershipPackage::class)->findOneBy(['uuid' => $uuid]);

        if (empty($package)) {
            // Set flash message
            $this->addFlash('danger', 'Acest conținut nu există.');
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
                $this->addFlash('danger', 'Această acțiune nu există.');
                return $this->redirectToRoute('dashboard_membership_index');
        }

        // Update data
        $em->persist($package);
        $em->flush();

        // Set flash message
        $this->addFlash('success', sprintf('Acest conținut a fost cu succes %s', $action === 'moderate' ? 'moderat' : 'șters'));
        return $this->redirectToRoute('dashboard_membership_index');
    }
}