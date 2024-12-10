<?php

namespace App\Helper;

use App\Entity\Article;
use App\Entity\Company;
use App\Entity\CompanyGallery;
use App\Entity\Event;
use App\Entity\EventGallery;
use App\Entity\EventIntroGallery;
use App\Entity\EventPartner;
use App\Entity\EventSpeaker;
use App\Entity\Job;
use App\Entity\TrainingCourse;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserHelper
{
    /** @var EntityManagerInterface */
    protected EntityManagerInterface $em;

    /** @var FileUploader */
    protected FileUploader $fileUploader;

    /** @var DefaultHelper */
    protected DefaultHelper $helper;

    /**
     * @param EntityManagerInterface $em
     * @param FileUploader $fileUploader
     * @param DefaultHelper $helper
     */
    public function __construct(EntityManagerInterface $em, FileUploader $fileUploader, DefaultHelper $helper)
    {
        $this->em = $em;
        $this->fileUploader = $fileUploader;
        $this->helper = $helper;
    }

    /**
     * @param User $user
     * @param bool $hasRemove
     * @return void
     */
    public function parseUserItems(User $user, bool $hasRemove): void
    {
        // User dates
        $articles = $user->getArticles();
        $courses = $user->getTrainingCourses();
        $jobs = $user->getJobs();
        $companies = $user->getCompanies();

        /**
         * Parse and delete/activate articles
         * @var Article $article
         */
        foreach ($articles as $article) {
            $article->setDeletedAt($hasRemove ? new \DateTime() : null);
            $article->setStatus(DefaultHelper::STATUS_DRAFT);

            $this->em->persist($article);
            $this->em->flush();
        }

        /**
         * Parse and delete/activate courses
         * @var TrainingCourse $course
         */
        foreach ($courses as $course) {
            $course->setDeletedAt($hasRemove ? new \DateTime() : null);
            $course->setStatus(DefaultHelper::STATUS_DRAFT);

            $this->em->persist($course);
            $this->em->flush();
        }

        /**
         * Parse and delete/activate jobs
         * @var Job $job
         */
        foreach ($jobs as $job) {
            $job->setDeletedAt($hasRemove ? new \DateTime() : null);
            $job->setStatus(DefaultHelper::STATUS_DRAFT);

            $this->em->persist($job);
            $this->em->flush();
        }

        /**
         * Parse and delete/activate companies
         * @var Company $company
         */
        foreach ($companies as $company) {
            $company->setDeletedAt($hasRemove ? new \DateTime() : null);
            $company->setStatus(DefaultHelper::STATUS_DRAFT);

            $this->em->persist($company);
            $this->em->flush();
        }
    }

    /**
     * @param object $entity
     * @param string $entityName
     * @return void
     */
    public function removeEntityFiles(object $entity, string $entityName): void
    {
        switch ($entityName) {
            case Article::ENTITY_NAME:
                /** @var Article $article */
                $article = $entity;

                // Remove @fileName
                $this->fileUploader->removeFile(
                    $this->helper->getEnvValue('app_article_path'),
                    $article->getFileName()
                );
                break;
            case TrainingCourse::ENTITY_NAME:
                /** @var TrainingCourse $course */
                $course = $entity;

                // Remove @fileName
                $this->fileUploader->removeFile(
                    $this->helper->getEnvValue('app_course_path'),
                    $course->getFileName()
                );
                break;
            case Job::ENTITY_NAME:
                /** @var Job $job */
                $job = $entity;

                // Remove @fileName
                $this->fileUploader->removeFile(
                    $this->helper->getEnvValue('app_job_path'),
                    $job->getFileName()
                );
                break;
            case EventPartner::ENTITY_NAME:
                /** @var EventPartner $eventPartner */
                $eventPartner = $entity;

                // Remove @fileName
                $this->fileUploader->removeFile(
                    $this->helper->getEnvValue('app_event_partner_path'),
                    $eventPartner->getFileName()
                );
                break;
            case EventSpeaker::ENTITY_NAME:
                /** @var EventSpeaker $eventSpeaker */
                $eventSpeaker = $entity;

                // Remove @fileName
                $this->fileUploader->removeFile(
                    $this->helper->getEnvValue('app_event_speaker_path'),
                    $eventSpeaker->getFileName()
                );
                break;
            case Event::ENTITY_NAME:
                /** @var Event $event */
                $event = $entity;

                // Define paths for event files and galleries
                $eventPath = $this->helper->getEnvValue('app_event_path');
                $galleryPath = $this->helper->getEnvValue('app_event_gallery_path');

                // Remove main file
                $this->fileUploader->removeFile($eventPath, $event->getFileName());

                // Remove video placeholder
                $this->fileUploader->removeFile($eventPath, $event->getVideoPlaceholder());

                // Remove program file
                $this->fileUploader->removeFile($eventPath, $event->getProgramFileName());

                /** @var EventGallery $gallery */
                foreach ($event->getEventGalleries() as $gallery) {
                    $this->fileUploader->removeFile($galleryPath, $gallery->getFileName());
                }

                /** @var EventIntroGallery $gallery */
                foreach ($event->getEventIntroGalleries() as $gallery) {
                    $this->fileUploader->removeFile($galleryPath, $gallery->getFileName());
                }
                break;
            case Company::ENTITY_NAME:
                /** @var Company $company */
                $company = $entity;

                // Define paths for company files and galleries
                $companyPath = $this->helper->getEnvValue('app_company_path');
                $galleryPath = $this->helper->getEnvValue('app_company_gallery_path');

                // Remove main file
                $this->fileUploader->removeFile($companyPath, $company->getFileName());


                // Remove logo
                $this->fileUploader->removeFile($companyPath, $company->getLogo());

                // Remove video placeholder
                $this->fileUploader->removeFile($companyPath, $company->getVideoPlaceholder());

                /**
                 * Remove all gallery items
                 * @var CompanyGallery $gallery
                 */
                foreach ($company->getCompanyGalleries() as $gallery) {
                    $this->fileUploader->removeFile($galleryPath, $gallery->getFileName());
                }

                /**
                 * Change all job status
                 * @var Job $job
                 */
                foreach ($company->getJobs() as $job) {
                    $job->setStatus(DefaultHelper::STATUS_DRAFT);

                    $this->em->persist($job);
                    $this->em->flush();
                }

                /**
                 * Change all course status
                 * @var TrainingCourse $course
                 */
                foreach ($company->getTrainingCourses() as $course) {
                    $course->setStatus(DefaultHelper::STATUS_DRAFT);

                    $this->em->persist($course);
                    $this->em->flush();
                }
                break;
        }
    }
}