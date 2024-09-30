<?php

namespace App\Controller\Dashboard;

use App\Entity\EventPartner;
use App\Form\Type\EventPartnerForm;
use App\Helper\FileUploader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventPartnerController extends AbstractController
{
    #[Route('/dashboard/event/partners', name: 'dashboard_partner_index')]
    public function index(): Response
    {
        return $this->render('dashboard/partner/index.html.twig');
    }

    #[Route('/dashboard/event/partner/create', name: 'dashboard_partner_create')]
    public function create(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, TranslatorInterface $translator): Response
    {
        $partner = new EventPartner();
        $form = $this->createForm(EventPartnerForm::class, $partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // get data from the form
            $file = $form->get('fileName')->getData();

            // Upload file
            $uploadFile = $fileUploader->uploadFile(
                $file,
                $form,
                $this->getParameter('app_event_partner_path')
            );

            // Check and set @filename
            if ($uploadFile['success']) {
                // Set fileName file
                $partner->setFileName($uploadFile['fileName']);
            }


            // save new item to DB
            $em->persist($partner);
            $em->flush();

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_item_added', [], 'messages'));

            return $this->redirectToRoute('dashboard_partner_index');
        }

        return $this->render('dashboard/partner/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.create_partner', [], 'messages')
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/event/partner/{uuid}/edit', name: 'dashboard_partner_edit')]
    public function edit(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, TranslatorInterface $translator, $uuid): Response
    {
        /** @var EventPartner $partner */
        $partner = $em->getRepository(EventPartner::class)->findOneBy(['uuid' => $uuid]);
        $fileUploaded = true;

        if (null === $partner) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_partner_index');
        }

        // init form & handle request data
        $form = $this->createForm(EventPartnerForm::class, $partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get data from the form
            $file = $form->get('fileName')->getData();

            // Check uploaded file
            if (isset($file)) {
                $fileUploaded = false;

                // Upload file
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_event_partner_path')
                );

                // Check and set @filename
                if ($uploadFile['success']) {
                    // Remove file
                    $fileUploaded = $fileUploader->removeFile($this->getParameter('app_event_partner_path'), $partner->getFileName());
                    $partner->setFileName($uploadFile['fileName']);
                }
            }

            if ($fileUploaded) {
                // save changes to DB
                $em->persist($partner);
                $em->flush();

                // Set flash message
                $this->addFlash('success', $translator->trans('controller.success_edit', [], 'messages'));

                return $this->redirectToRoute('dashboard_partner_index');
            }
        }

        return $this->render('dashboard/partner/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.edit_partner', [], 'messages'),
            'fileName' => $partner->getFileName()
        ]);
    }

    #[Route('/dashboard/event/partner/actions/{action}/{uuid}', name: 'dashboard_partner_actions')]
    public function actions(EntityManagerInterface $em, TranslatorInterface $translator, $action, $uuid): Response
    {
        /** @var EventPartner $partner */
        $partner = $em->getRepository(EventPartner::class)->findOneBy(['uuid' => $uuid]);

        if (!isset($partner)) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_partner_index');
        }

        if ($action === 'remove') {
            // Soft delete
            $partner->setDeletedAt(new DateTime());
        } else {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.error_action', [], 'messages'));
            return $this->redirectToRoute('dashboard_partner_index');
        }

        // Update data
        $em->persist($partner);
        $em->flush();

        // Set flash message
        $this->addFlash('success', sprintf($translator->trans('controller.success_multiple', [], 'messages'), $action === 'moderate' ? $translator->trans('controller.moderated', [], 'messages') : $translator->trans('controller.deleted', [], 'messages')));

        // Redirect to listing page
        return $this->redirectToRoute('dashboard_partner_index');
    }
}
