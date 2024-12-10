<?php

namespace App\Controller\Dashboard;

use App\Entity\TrainingCourse;
use App\Entity\TrainingCourseTranslation;
use App\Entity\User;
use App\Form\Type\TrainingCourseFormType;
use App\Helper\DefaultHelper;
use App\Helper\MembershipHelper;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use App\Helper\UserHelper;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class TrainingController extends AbstractController
{
    #[Route('/dashboard/courses', name: 'dashboard_training_index')]
    public function index(): Response
    {
        return $this->render('dashboard/training/index.html.twig');
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/course/create', name: 'dashboard_training_create')]
    public function create(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, TranslatorInterface $translator, MembershipHelper $helper): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $course = new TrainingCourse();

        // Get membership by @user
        $getPlan = $helper->checkMembership($user, TrainingCourse::ENTITY_NAME);

        // Check status plan
        if ($getPlan['status']) {
            $this->addFlash('danger', sprintf($translator->trans('dashboard.actions.max_plan', [], 'messages'), $getPlan['membership'], $getPlan['max']));
            return $this->redirectToRoute('dashboard_training_index');
        }

        /** @var User $userAdm */
        $userAdm = $this->isGranted('ROLE_ADMIN') ? null : $user;

        $form = $this->createForm(TrainingCourseFormType::class, $course, [
            'language' => $languageHelper->getDefaultLanguage(),
            'user' => $userAdm
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get data from the form
            $file = $form->get('fileName')->getData();

            // Get default language
            $language = $languageHelper->getDefaultLanguage();

            // Create translation and set data
            $courseTranslation = new TrainingCourseTranslation();
            $courseTranslation->setTrainingCourse($course);
            $courseTranslation->setLanguage($language);
            $courseTranslation->setTitle($form->get('title')->getData());
            $courseTranslation->setDuration($form->get('duration')->getData());
            $courseTranslation->setLevel($form->get('level')->getData());
            $courseTranslation->setCertificate($form->get('certificate')->getData());
            $courseTranslation->setBody($form->get('body')->getData());
            $courseTranslation->setShortDescription($form->get('shortDescription')->getData());

            $course->setUser($user);
            $course->addTrainingCourseTranslation($courseTranslation);
            $course->setStartCourseDate(new DateTime($form->get('startCourseDate')->getData()));
            $course->setEndedAt(new DateTime($form->get('endedAt')->getData()));

            if (isset($file)) {
                // Upload company file
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_course_path')
                );

                // Check and set @filename
                if ($uploadFile['success']) {
                    // Set image file
                    $course->setFileName($uploadFile['fileName']);
                }
            }

            // Save to DB
            $em->persist($course);
            $em->persist($courseTranslation);
            $em->flush();


            // Insert item in EntityLog
            $helper->insertEntityLog($course, TrainingCourse::ENTITY_NAME);

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_item_added', [], 'messages'));
            return $this->redirectToRoute('dashboard_training_index');
        }

        return $this->render('dashboard/training/actions.html.twig', [
            'pageTitle' => $translator->trans('controller.create_course', [], 'messages'),
            'form' => $form->createView()
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/course/{uuid}/edit', name: 'dashboard_training_edit')]
    public function edit(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, TranslatorInterface $translator, UserHelper $userHelper, $uuid): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        /** @var TrainingCourse $course */
        $course = $em->getRepository(TrainingCourse::class)->findOneBy(['uuid' => $uuid]);

        if (empty($course)) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));

            return $this->redirectToRoute('dashboard_training_index');
        }

        // Get selected language
        $locale = $request->get('locale');
        $language = $languageHelper->getLanguageByLocale($locale);

        // Get translation
        $courseTranslation = $em->getRepository(TrainingCourseTranslation::class)->findOneBy([
            'trainingCourse' => $course,
            'language' => $language
        ]);

        $courseTranslation = $courseTranslation ?? new TrainingCourseTranslation();

        /** @var User $userAdm */
        $userAdm = $this->isGranted('ROLE_ADMIN') ? null : $user;

        // init form & handle request data
        $form = $this->createForm(TrainingCourseFormType::class, $course, [
            'translation' => $courseTranslation,
            'language' => $languageHelper->getDefaultLanguage(),
            'user' => $userAdm
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileUploaded = true;

            // get data from the form
            $file = $form->get('fileName')->getData();

            // update translation and set data
            $courseTranslation->setTitle($form->get('title')->getData());
            $courseTranslation->setDuration($form->get('duration')->getData());
            $courseTranslation->setLevel($form->get('level')->getData());
            $courseTranslation->setCertificate($form->get('certificate')->getData());
            $courseTranslation->setBody($form->get('body')->getData());
            $courseTranslation->setShortDescription($form->get('shortDescription')->getData());
            $courseTranslation->setTrainingCourse($course);
            $courseTranslation->setLanguage($language);

            $course->setStartCourseDate(new DateTime($form->get('startCourseDate')->getData()));
            $course->setEndedAt(new DateTime($form->get('endedAt')->getData()));
            $course->setUpdatedAt(new DateTime());

            // Check uploaded file
            if (isset($file)) {
                // Upload company file
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_course_path')
                );

                // Set status uploaded
                $fileUploaded = $uploadFile['success'];

                // Check and set @filename
                if ($uploadFile['success']) {
                    // Remove old file
                    $userHelper->removeEntityFiles($course, TrainingCourse::ENTITY_NAME);

                    // Set fileName
                    $course->setFileName($uploadFile['fileName']);
                }
            }

            if ($fileUploaded) {
                // save changes to DB
                $em->persist($course);
                $em->persist($courseTranslation);
                $em->flush();

                // Set flash message
                $this->addFlash('success', $translator->trans('controller.success_edit', [], 'messages') . ' - ' . $courseTranslation->getTitle());
                return $this->redirectToRoute('dashboard_training_index');
            }
        }

        return $this->render('dashboard/training/actions.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => $translator->trans('controller.edit_course', [], 'messages'),
            'image' => $course->getFileName(),
            'startCourseDate' => $course->getStartCourseDate()->format('d.m.Y'),
            'endedAt' => $course->getEndedAt()->format('d.m.Y')
        ]);
    }

    #[Route('/dashboard/course/actions/{action}/{uuid}', name: 'dashboard_training_actions')]
    public function actions(EntityManagerInterface $em, TranslatorInterface $translator, MembershipHelper $helper, UserHelper $userHelper, $action, $uuid): Response
    {
        /** @var TrainingCourse $course */
        $course = $em->getRepository(TrainingCourse::class)->findOneBy(['uuid' => $uuid]);

        if ($course === null || $course->getCompany() === null && $action === DefaultHelper::ACTION_MODERATE) {
            // Set flash message
            $this->addFlash('danger', $translator->trans($course->getCompany() === null ? 'controller.no_company' : 'controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_training_index');
        }

        /** @var User $user */
        $user = $course->getUser();

        // Get membership by @user
        $getPlan = $helper->checkMembership($user, TrainingCourse::ENTITY_NAME);

        // Check status
        if ($getPlan['status'] && $course->getStatus() !== DefaultHelper::STATUS_PUBLISHED && $action === 'moderate') {
            $this->addFlash('danger', sprintf($translator->trans('dashboard.actions.max_plan', [], 'messages'), $getPlan['membership'], $getPlan['max']));
            return $this->redirectToRoute('dashboard_article_index');
        }

        switch ($action) {
            case DefaultHelper::ACTION_REMOVE:
                // Remove storage files
                $userHelper->removeEntityFiles($course, TrainingCourse::ENTITY_NAME);

                // Remove item
                $em->remove($course);
                break;
            case DefaultHelper::ACTION_MODERATE:
                $course->setStatus($course->getStatus() === DefaultHelper::STATUS_DRAFT ? DefaultHelper::STATUS_PUBLISHED : DefaultHelper::STATUS_DRAFT);
                $em->persist($course);
                break;
            default:
                // Set flash message
                $this->addFlash('danger', $translator->trans('controller.error_action', [], 'messages'));
                return $this->redirectToRoute('dashboard_training_index');
        }

        // Update data
        $em->flush();

        // Set flash message
        $this->addFlash('success', sprintf($translator->trans('controller.success_multiple', [], 'messages'), $action === 'moderate' ? $translator->trans('controller.moderated', [], 'messages') : $translator->trans('controller.deleted', [], 'messages')));

        // Redirect to listing page
        return $this->redirectToRoute('dashboard_training_index');
    }
}
