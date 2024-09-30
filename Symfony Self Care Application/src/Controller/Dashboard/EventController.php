<?php

namespace App\Controller\Dashboard;

use App\Entity\Event;
use App\Entity\EventTranslation;
use App\Entity\EventWinner;
use App\Form\Type\EventForm;
use App\Helper\DefaultHelper;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventController extends AbstractController
{
    #[Route('/dashboard/secure/event', name: 'dashboard_event_index')]
    public function index(): Response
    {
        return $this->render('dashboard/event/index.html.twig');
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/secure/event/create', name: 'dashboard_event_create')]
    public function create(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, TranslatorInterface $translator): Response
    {
        // get default language
        $language = $languageHelper->getDefaultLanguage();
        $order = 1;

        $event = new Event();
        $form = $this->createForm(EventForm::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // get data from the form
            $files = DefaultHelper::EVENT_FILE_FIELDS;
            $winners = $form->get('eventWinners')->getData();

            // create translation and set data
            $eventTranslation = new EventTranslation();
            $eventTranslation->setEvent($event);
            $eventTranslation->setLanguage($language);
            $eventTranslation->setTitle($form->get('title')->getData());
            $eventTranslation->setDescription($form->get('description')->getData());
            $eventTranslation->setShortDescription($form->get('shortDescription')->getData());
            $event->setStartDate(new DateTime($form->get('startDate')->getData()));

            $event->addEventTranslation($eventTranslation);

            // Parse and save files
            foreach ($files as $value) {
                $file = $form->get($value)->getData();

                if (isset($file)) {
                    // Upload file
                    $uploadFile = $fileUploader->uploadFile(
                        $file,
                        $form,
                        $this->getParameter('app_event_path'),
                        $value
                    );

                    // Check and set @filename
                    if ($uploadFile['success']) {
                        match ($value) {
                            DefaultHelper::EVENT_FILE_FIELDS['video'] => $event->setVideoPlaceholder($uploadFile['fileName']),
                            DefaultHelper::EVENT_FILE_FIELDS['preview'] => $event->setFileName($uploadFile['fileName']),
                            DefaultHelper::EVENT_FILE_FIELDS['program'] => $event->setProgramFileName($uploadFile['fileName'])
                        };
                    }
                }
            }

            // save new item to DB
            $em->persist($event);
            $em->persist($eventTranslation);
            $em->flush();

            // Check exist winners
            if (!empty($winners)) {
                foreach ($winners as $item) {
                    $winner = new EventWinner();
                    $winner->setEvent($event);
                    $winner->setCompany($item);
                    $winner->setPosition($order++);

                    // save winner to DB
                    $em->persist($winner);
                    $em->flush();
                }
            }

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_item_added', [], 'messages'));
            return $this->redirectToRoute('dashboard_event_index');
        }

        return $this->render('dashboard/event/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.create_event', [], 'messages')
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/secure/event/{uuid}/edit', name: 'dashboard_event_edit')]
    public function edit(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, $uuid, TranslatorInterface $translator): Response
    {
        /** @var Event $event */
        $event = $em->getRepository(Event::class)->findOneBy(['uuid' => $uuid]);
        $order = 1;

        if (null === $event) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_event_index');
        }

        // get selected language
        $locale = $request->get('locale');
        $language = $languageHelper->getLanguageByLocale($locale);
        $files = DefaultHelper::EVENT_FILE_FIELDS;
        $fileUploaded = true;

        // get translation
        $eventTranslation = $em->getRepository(EventTranslation::class)->findOneBy([
            'event' => $event,
            'language' => $language
        ]);

        $eventTranslation = $eventTranslation ?? new EventTranslation();

        // Get selected winners
        $selectedCompanies = $event->getEventWinners()->map(function ($eventWinner) {
            return $eventWinner->getCompany();
        })->toArray();

        // init form & handle request data
        $form = $this->createForm(EventForm::class, $event, ['translation' => $eventTranslation, 'eventWinners' => $selectedCompanies]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $winnersOld = $event->getEventWinners();
            $winners = $form->get('eventWinners')->getData();

            // Check exist winners
            if (!empty($winnersOld)) {
                foreach ($winnersOld as $item) {
                    $event->removeEventWinner($item);
                }
            }

            $eventTranslation->setTitle($form->get('title')->getData());
            $eventTranslation->setDescription($form->get('description')->getData());
            $eventTranslation->setShortDescription($form->get('shortDescription')->getData());
            $event->setStartDate(new DateTime($form->get('startDate')->getData()));

            $eventTranslation->setEvent($event);
            $eventTranslation->setLanguage($language);

            // Parse and save files
            foreach ($files as $value) {
                $file = $form->get($value)->getData();

                if (isset($file)) {
                    $fileUploaded = false;

                    // Upload file
                    $uploadFile = $fileUploader->uploadFile(
                        $file,
                        $form,
                        $this->getParameter('app_event_path'),
                        $value
                    );

                    // Check and set @filename
                    if ($uploadFile['success']) {
                        switch ($value) {
                            case DefaultHelper::EVENT_FILE_FIELDS['video']:
                                // Remove file
                                $fileUploaded = $fileUploader->removeFile($this->getParameter('app_event_path'), $event->getVideoPlaceholder());

                                $event->setVideoPlaceholder($uploadFile['fileName']);
                                break;
                            case DefaultHelper::EVENT_FILE_FIELDS['preview']:
                                // Remove file
                                $fileUploaded = $fileUploader->removeFile($this->getParameter('app_event_path'), $event->getFileName());

                                $event->setFileName($uploadFile['fileName']);
                                break;
                            case DefaultHelper::EVENT_FILE_FIELDS['program']:
                                // Remove file
                                $fileUploaded = $fileUploader->removeFile($this->getParameter('app_event_path'), $event->getProgramFileName());

                                $event->setProgramFileName($uploadFile['fileName']);
                                break;
                        }
                    }
                }
            }

            if ($fileUploaded) {
                // save changes to DB
                $event->setUpdatedAt(new DateTime());
                $em->persist($event);
                $em->persist($eventTranslation);
                $em->flush();

                // Check exist winners
                if (!empty($winners)) {
                    foreach ($winners as $item) {
                        $winner = new EventWinner();
                        $winner->setEvent($event);
                        $winner->setCompany($item);
                        $winner->setPosition($order++);

                        // save winner to DB
                        $em->persist($winner);
                        $em->flush();
                    }
                }

                // Set flash message
                $this->addFlash('success', $translator->trans('controller.success_edit', [], 'messages'));
                return $this->redirectToRoute('dashboard_event_index');
            }
        }

        return $this->render('dashboard/event/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.edit_event', [], 'messages'),
            'uuid' => $event->getUuid(),
            'galleries' => $event->getEventGalleries(),
            'galleryIntro' => $event->getEventIntroGalleries(),
            'fileName' => $event->getFileName(),
            'videoPlaceholder' => $event->getVideoPlaceholder(),
            'programFileName' => $event->getProgramFileName(),
            'startDate' => $event->getStartDate()->format('d.m.Y H:i')
        ]);
    }

    #[Route('/dashboard/secure/event/actions/{action}/{uuid}', name: 'dashboard_event_actions')]
    public function actions(EntityManagerInterface $em, $action, $uuid, TranslatorInterface $translator): Response
    {
        /** @var Event $event */
        $event = $em->getRepository(Event::class)->findOneBy(['uuid' => $uuid]);

        if (!isset($event)) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_event_index');
        }

        switch ($action) {
            case 'remove':
                // Soft delete
                $event->setDeletedAt(new DateTime());
                break;
            case 'moderate':
                // Update status
                $event->setStatus($event->getStatus() === DefaultHelper::STATUS_DRAFT ? DefaultHelper::STATUS_PUBLISHED : DefaultHelper::STATUS_DRAFT);
                break;
            default:
                // Set flash message and redirect
                $this->addFlash('danger', $translator->trans('controller.error_action', [], 'messages'));
                return $this->redirectToRoute('dashboard_event_index');
        }

        // Update data
        $em->persist($event);
        $em->flush();

        // Set flash message
        $this->addFlash('success', sprintf($translator->trans('controller.success_multiple', [], 'messages'), $action === 'moderate' ? $translator->trans('controller.moderated', [], 'messages') : $translator->trans('controller.deleted', [], 'messages')));
        return $this->redirectToRoute('dashboard_event_index');
    }
}