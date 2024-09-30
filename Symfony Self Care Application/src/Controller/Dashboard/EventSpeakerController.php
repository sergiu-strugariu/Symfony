<?php

namespace App\Controller\Dashboard;

use App\Entity\EventSpeaker;
use App\Form\Type\EventSpeakerForm;
use App\Helper\DefaultHelper;
use App\Helper\FileUploader;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventSpeakerController extends AbstractController
{
    #[Route('/dashboard/event/speakers', name: 'dashboard_speaker_index')]
    public function index(): Response
    {
        return $this->render('dashboard/speaker/index.html.twig');
    }

    #[Route('/dashboard/event/speaker/create', name: 'dashboard_speaker_create')]
    public function create(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, TranslatorInterface $translator): Response
    {
        $speaker = new EventSpeaker();
        $form = $this->createForm(EventSpeakerForm::class, $speaker);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // get data from the form
            $file = $form->get('fileName')->getData();

            if (isset($file)) {
                // Upload file
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_event_speaker_path')
                );

                // Check and set @filename
                if ($uploadFile['success']) {
                    // Set fileName file
                    $speaker->setFileName($uploadFile['fileName']);
                }
            }

            // save new item to DB
            $em->persist($speaker);
            $em->flush();

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_item_added', [], 'messages'));

            return $this->redirectToRoute('dashboard_speaker_index');
        }

        return $this->render('dashboard/speaker/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.create_speaker', [], 'messages')
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/event/speaker/{uuid}/edit', name: 'dashboard_speaker_edit')]
    public function edit(Request $request, EntityManagerInterface $em, FileUploader $fileUploader, TranslatorInterface $translator, $uuid): Response
    {
        /** @var EventSpeaker $speaker */
        $speaker = $em->getRepository(EventSpeaker::class)->findOneBy(['uuid' => $uuid]);
        $fileUploaded = true;

        if (null === $speaker) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_speaker_index');
        }

        // init form & handle request data
        $form = $this->createForm(EventSpeakerForm::class, $speaker);
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
                    $this->getParameter('app_event_speaker_path')
                );

                // Check and set @filename
                if ($uploadFile['success']) {
                    // Remove file
                    $fileUploaded = $fileUploader->removeFile($this->getParameter('app_event_speaker_path'), $speaker->getFileName());

                    $speaker->setFileName($uploadFile['fileName']);
                }
            }

            if ($fileUploaded) {
                // save changes to DB
                $speaker->setUpdatedAt(new DateTime());
                $em->persist($speaker);
                $em->flush();

                // Set flash message
                $this->addFlash('success', $translator->trans('controller.success_edit', [], 'messages'));

                return $this->redirectToRoute('dashboard_speaker_index');
            }
        }

        return $this->render('dashboard/speaker/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.edit_speaker', [], 'messages'),
            'fileName' => $speaker->getFileName()
        ]);
    }

    #[Route('/dashboard/event/speaker/actions/{action}/{uuid}', name: 'dashboard_speaker_actions')]
    public function actions(EntityManagerInterface $em, TranslatorInterface $translator, $action, $uuid): Response
    {
        /** @var EventSpeaker $speaker */
        $speaker = $em->getRepository(EventSpeaker::class)->findOneBy(['uuid' => $uuid]);

        if (!isset($speaker)) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_speaker_index');
        }

        if ($action === 'remove') {
            // Soft delete
            $speaker->setDeletedAt(new DateTime());
        } elseif ($action === 'moderate') {
            $speaker->setStatus($speaker->getStatus() === DefaultHelper::STATUS_DRAFT ? DefaultHelper::STATUS_PUBLISHED : DefaultHelper::STATUS_DRAFT);
        } else {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.error_action', [], 'messages'));
            return $this->redirectToRoute('dashboard_speaker_index');
        }

        // Update data
        $em->persist($speaker);
        $em->flush();

        // Set flash message
        $this->addFlash('success', sprintf($translator->trans('controller.success_multiple', [], 'messages'), $action === 'moderate' ? $translator->trans('controller.moderated', [], 'messages') : $translator->trans('controller.deleted', [], 'messages')));

        // Redirect to listing page
        return $this->redirectToRoute('dashboard_speaker_index');
    }
}
