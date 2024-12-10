<?php

namespace App\Controller\Dashboard;

use App\Entity\User;
use App\Helper\DefaultHelper;
use App\Helper\MembershipHelper;
use App\Helper\UserHelper;
use DateTime;
use App\Entity\Job;
use App\Entity\JobTranslation;
use App\Form\Type\JobFormType;
use App\Helper\FileUploader;
use App\Helper\LanguageHelper;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

class JobController extends AbstractController
{
    #[Route('/dashboard/jobs', name: 'dashboard_job_index')]
    public function index(): Response
    {
        return $this->render('dashboard/job/index.html.twig');
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/job/create', name: 'dashboard_job_create')]
    public function create(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, TranslatorInterface $translator, MembershipHelper $helper): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Get membership by @user
        $getPlan = $helper->checkMembership($user, Job::ENTITY_NAME);

        // Check status plan
        if ($getPlan['status']) {
            $this->addFlash('danger', sprintf($translator->trans('dashboard.actions.max_plan', [], 'messages'), $getPlan['membership'], $getPlan['max']));
            return $this->redirectToRoute('dashboard_job_index');
        }

        $job = new Job();
        $benefitsArray = [];

        /** @var User $userAdm */
        $userAdm = $this->isGranted('ROLE_ADMIN') ? null : $user;

        $form = $this->createForm(JobFormType::class, $job, ['language' => $languageHelper->getDefaultLanguage(), 'user' => $userAdm]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $job->setUser($user);
            $job->setEndedAt(new DateTime($form->get('endedAt')->getData()));

            // get data from the form
            $file = $form->get('fileName')->getData();

            // get default language
            $language = $languageHelper->getDefaultLanguage();

            $benefits = json_decode($form->get('benefits')->getData(), true);

            // Parse data
            foreach ($benefits as $item) {
                $benefitsArray[] = $item['value'];
            }

            // create translation and set data
            $jobTranslation = new JobTranslation();
            $jobTranslation->setJob($job);
            $jobTranslation->setLanguage($language);
            $jobTranslation->setTitle($form->get('title')->getData());
            $jobTranslation->setBody($form->get('body')->getData());
            $jobTranslation->setBenefits($benefitsArray);
            $jobTranslation->setShortDescription($form->get('shortDescription')->getData());
            $job->addJobTranslation($jobTranslation);

            if (isset($file)) {
                // Upload company file
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_job_path'),
                    'fileName'
                );
                // Check and set @filename
                if ($uploadFile['success']) {
                    // Set image file
                    $job->setFileName($uploadFile['fileName']);
                }
            }

            // Save changes to DB
            $em->persist($job);
            $em->persist($jobTranslation);
            $em->flush();

            // Insert item in EntityLog
            $helper->insertEntityLog($job, Job::ENTITY_NAME);

            // Set flash message
            $this->addFlash('success', $translator->trans('controller.success_item_added', [], 'messages'));
            return $this->redirectToRoute('dashboard_job_index');
        }

        return $this->render('dashboard/job/actions.html.twig', [
            'form' => $form->createView(),
            'benefits' => json_encode(Job::getBenefits(), JSON_UNESCAPED_UNICODE),
            'pageTitle' => $translator->trans('controller.create_job', [], 'messages')
        ]);
    }

    /**
     * @throws Exception
     */
    #[Route('/dashboard/job/{uuid}/edit', name: 'dashboard_job_edit')]
    public function edit(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, TranslatorInterface $translator, UserHelper $userHelper, $uuid): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $job = $em->getRepository(Job::class)->findOneBy(['uuid' => $uuid]);
        $benefitsArray = [];

        if (null === $job) {
            // Set flash message
            $this->addFlash('danger', $translator->trans('controller.no_content', [], 'messages'));

            return $this->redirectToRoute('dashboard_job_index');
        }

        // get selected language
        $locale = $request->get('locale');
        $language = $languageHelper->getLanguageByLocale($locale);

        /** @var User $userAdm */
        $userAdm = $this->isGranted('ROLE_ADMIN') ? null : $user;

        // get translation
        $jobTranslation = $em->getRepository(JobTranslation::class)->findOneBy([
            'job' => $job,
            'language' => $language
        ]);

        $jobTranslation = $jobTranslation ?? new JobTranslation();

        // init form & handle request data
        $form = $this->createForm(JobFormType::class, $job, [
            'translation' => $jobTranslation,
            'language' => $language,
            'user' => $userAdm
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileUploaded = true;

            $benefits = json_decode($form->get('benefits')->getData(), true);

            // Parse data
            foreach ($benefits as $item) {
                $benefitsArray[] = $item['value'];
            }

            // Get data from the form
            $file = $form->get('fileName')->getData();

            // Update translation and set data
            $job->setUpdatedAt(new DateTime());
            $job->setEndedAt(new DateTime($form->get('endedAt')->getData()));
            $jobTranslation->setTitle($form->get('title')->getData());
            $jobTranslation->setBody($form->get('body')->getData());
            $jobTranslation->setBenefits($benefitsArray);
            $jobTranslation->setShortDescription($form->get('shortDescription')->getData());
            $jobTranslation->setJob($job);
            $jobTranslation->setLanguage($language);

            // Check uploaded file
            if (isset($file)) {
                // Upload company file
                $uploadFile = $fileUploader->uploadFile(
                    $file,
                    $form,
                    $this->getParameter('app_job_path')
                );

                // Set status uploaded
                $fileUploaded = $uploadFile['success'];

                // Check and set @filename
                if ($uploadFile['success']) {
                    // Remove old file
                    $userHelper->removeEntityFiles($job, Job::ENTITY_NAME);

                    // Set fileName
                    $job->setFileName($uploadFile['fileName']);
                }
            }

            if ($fileUploaded) {
                // save changes to DB
                $em->persist($job);
                $em->persist($jobTranslation);
                $em->flush();

                // Set flash message
                $this->addFlash('success', $translator->trans('controller.success_edit', [], 'messages') . ' - ' . $jobTranslation->getTitle());
                return $this->redirectToRoute('dashboard_job_index');
            }
        }

        return $this->render('dashboard/job/actions.html.twig', [
            'form' => $form->createView(),
            'benefits' => json_encode(Job::getBenefits(), JSON_UNESCAPED_UNICODE),
            'pageTitle' => $translator->trans('controller.edit_job', [], 'messages'),
            'fileName' => $job->getFileName(),
            'endedAt' => $job->getEndedAt()->format('d.m.Y')
        ]);
    }

    #[Route('/dashboard/job/actions/{action}/{uuid}', name: 'dashboard_job_actions')]
    public function actions(EntityManagerInterface $em, TranslatorInterface $translator, MembershipHelper $helper, UserHelper $userHelper, $action, $uuid): Response
    {
        /** @var Job $job */
        $job = $em->getRepository(Job::class)->findOneBy(['uuid' => $uuid]);

        if ($job === null || $job->getCompany() === null && $action === DefaultHelper::ACTION_MODERATE) {
            // Set flash message
            $this->addFlash('danger', $translator->trans($job->getCompany() === null ? 'controller.no_company' : 'controller.no_content', [], 'messages'));
            return $this->redirectToRoute('dashboard_job_index');
        }

        /** @var User $user */
        $user = $job->getUser();

        // Get membership by @user
        $getPlan = $helper->checkMembership($user, Job::ENTITY_NAME);

        // Check status
        if ($getPlan['status'] && $job->getStatus() !== DefaultHelper::STATUS_PUBLISHED && $action === DefaultHelper::ACTION_MODERATE) {
            $this->addFlash('danger', sprintf($translator->trans('dashboard.actions.max_plan', [], 'messages'), $getPlan['membership'], $getPlan['max']));
            return $this->redirectToRoute('dashboard_job_index');
        }

        switch ($action) {
            case DefaultHelper::ACTION_REMOVE:
                // Remove storage files
                $userHelper->removeEntityFiles($job, Job::ENTITY_NAME);

                // Remove item
                $em->remove($job);
                break;
            case DefaultHelper::ACTION_MODERATE:
                $job->setStatus($job->getStatus() === DefaultHelper::STATUS_DRAFT ? DefaultHelper::STATUS_PUBLISHED : DefaultHelper::STATUS_DRAFT);
                $em->persist($job);
                break;
            default:
                // Set flash message
                $this->addFlash('danger', $translator->trans('controller.error_action', [], 'messages'));
                return $this->redirectToRoute('dashboard_job_index');
        }

        // Update data
        $em->flush();

        // Set flash message
        $this->addFlash('success', sprintf($translator->trans('controller.success_multiple', [], 'messages'), $action === 'moderate' ? $translator->trans('controller.moderated', [], 'messages') : $translator->trans('controller.deleted', [], 'messages')));

        // Redirect to listing page
        return $this->redirectToRoute('dashboard_job_index');
    }
}
