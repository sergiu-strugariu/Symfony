<?php

namespace App\Controller\Dashboard;

use App\Entity\Education;
use App\Entity\EducationSchedule;
use App\Entity\EducationScheduleTranslation;
use App\Entity\EducationTranslation;
use App\Helper\LanguageHelper;
use App\Helper\FileUploader;
use App\Form\Type\EducationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;


class EducationController extends AbstractController
{
    
    #[Route('/dashboard/education/create/{type}', name: 'dashboard_education_create', requirements: ['type' => 'course|workshop|convention'])]
    public function create(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, $type): Response
    {
        $languages = $languageHelper->getAllLanguages();

        $education = new Education();

        $form = $this->createForm(EducationType::class, $education, ['locale' => $languageHelper->getDefaultLanguage()->getLocale()]);
        $form->handleRequest($request);

        $listingRoute = $this->getListingRouteByEducationType($type);

        if ($form->isSubmitted() && $form->isValid()) {
            $education->setUuid(Uuid::v4());
            $education->setType($type);

            foreach ($languages as $language) {
                $educationTranslation = new EducationTranslation();

                $educationTranslation->setEducation($education);
                $educationTranslation->setLanguage($language);
                $educationTranslation->setTitle($form->get('title')->getData());
                $educationTranslation->setDescription($form->get('description')->getData());
                $educationTranslation->setShortDescription($form->get('shortDescription')->getData());
                $educationTranslation->setAdditionalInfo($form->get('additionalInfo')->getData());
                $educationTranslation->setImportantInfo($form->get('importantInfo')->getData());

                $em->persist($educationTranslation);
            }

            $file = $form->get('image')->getData();

            if ($file instanceof UploadedFile) {
                $uploadFile = $fileUploader->uploadFile($file, $form, $this->getParameter('app_education_path'));
                if ($uploadFile['success']) $education->setImageName($uploadFile['fileName']);
            }

            $em->persist($education);

            $schedules = $request->get('newSchedule');

            if ($schedules) {
                foreach ($schedules as $schedule) {
                    $educationSchedule = new EducationSchedule();

                    $educationSchedule->setEducation($education);
                    $educationSchedule->setStartDate(new \DateTime($schedule['startDate']));
                    $educationSchedule->setEndDate(new \DateTime($schedule['endDate']));

                    foreach ($languages as $language) {
                        $educationScheduleTranslation = new EducationScheduleTranslation();

                        $educationScheduleTranslation->setEducationSchedule($educationSchedule);
                        $educationScheduleTranslation->setLanguage($language);
                        $educationScheduleTranslation->setDescription($schedule['description']);

                        $em->persist($educationScheduleTranslation);
                    }

                    $em->persist($educationSchedule);
                }
            }

            $em->flush();

            $this->addFlash('success', 'Congratulations, you have successfully added a new education.');
            return $this->redirectToRoute($listingRoute);
        }

        return $this->render('dashboard/education/management.html.twig', [
            'form' => $form->createView(),
            'listing_route' => $listingRoute,
            'editMode' => false
        ]);
    }
    
    #[Route('/dashboard/education/{uuid}/edit', name: 'dashboard_education_edit')]
    public function edit(Request $request, EntityManagerInterface $em, LanguageHelper $languageHelper, FileUploader $fileUploader, $uuid): Response
    {
        $education = $em->getRepository(Education::class)->findOneBy(['uuid' => $uuid]);
        if (null === $education) {
            return $this->redirectToRoute('dashboard_index');
        }
            
        $listingRoute = $this->getListingRouteByEducationType($education->getType());
        $locale = $request->get('locale', $this->getParameter('default_locale'));
        $language = $languageHelper->getLanguageByLocale($locale);

        $educationTranslation = $em->getRepository(EducationTranslation::class)->findOneBy([
            'education' => $education,
            'language' => $language
        ]);

        if (null === $educationTranslation) {
            $educationTranslation = new EducationTranslation();
            $educationTranslation->setEducation($education);
            $educationTranslation->setLanguage($language);
        }

        $form = $this->createForm(EducationType::class, $education, [
            'translation' => $educationTranslation,
            'locale' => $locale
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $educationTranslation->setTitle($form->get('title')->getData());
            $educationTranslation->setDescription($form->get('description')->getData());
            $educationTranslation->setShortDescription($form->get('shortDescription')->getData());
            $educationTranslation->setAdditionalInfo($form->get('additionalInfo')->getData());
            $educationTranslation->setImportantInfo($form->get('importantInfo')->getData());

            $file = $form->get('image')->getData();

            if ($file instanceof UploadedFile) {
                $uploadFile = $fileUploader->uploadFile($file, $form, $this->getParameter('app_education_path'));
                if ($uploadFile['success']) $education->setImageName($uploadFile['fileName']);
            }

            $em->persist($education);
            $em->persist($educationTranslation);

            $schedules = $request->get('schedules');
            $newSchedules = $request->get('newSchedule');

            if ($newSchedules) {
                foreach ($newSchedules as $newSchedule) {
                    $educationSchedule = new EducationSchedule();
                    $educationSchedule->setEducation($education);
                    $educationSchedule->setStartDate(new \DateTime($newSchedule['startDate']));
                    $educationSchedule->setEndDate(new \DateTime($newSchedule['endDate']));

                    $educationScheduleTranslation = new EducationScheduleTranslation();
                    $educationScheduleTranslation->setLanguage($language);
                    $educationScheduleTranslation->setEducationSchedule($educationSchedule);
                    $educationScheduleTranslation->setDescription($newSchedule['description']);

                    $em->persist($educationSchedule);
                    $em->persist($educationScheduleTranslation);
                }

                $em->flush();
            }

            if ($schedules) {
                foreach ($schedules as $key => $schedule) {
                    $educationSchedule = $em->getRepository(EducationSchedule::class)->findOneBy([
                        'id' => $key,
                        'education' => $education
                    ]);

                    if (!$educationSchedule) {
                        $educationSchedule = new EducationSchedule();
                        $educationSchedule->setEducation($education);
                    }

                    $educationSchedule->setStartDate(new \DateTime($schedule['startDate']));
                    $educationSchedule->setEndDate(new \DateTime($schedule['endDate']));
                    $em->persist($educationSchedule);

                    $educationScheduleTranslation = $em->getRepository(EducationScheduleTranslation::class)->findOneBy([
                        'educationSchedule' => $educationSchedule,
                        'language' => $language,
                    ]);

                    if (null === $educationScheduleTranslation) {
                        $educationScheduleTranslation = new EducationScheduleTranslation();
                        $educationScheduleTranslation->setEducationSchedule($educationSchedule);
                        $educationScheduleTranslation->setLanguage($language);
                    }

                    $educationScheduleTranslation->setDescription($schedule['description']);

                    $em->persist($educationScheduleTranslation);
                }
            }

            $em->flush();

            $this->addFlash('success', 'You have successfully edited the education');
            return $this->redirectToRoute($listingRoute);
        }
       
        return $this->render('dashboard/education/management.html.twig', [
            'form' => $form->createView(),
            'listing_route' => $listingRoute,
            'image' => $education->getImageName(),
            'entity' => $education,
            'locale' => $locale,
            'editMode' => true
        ]);
    }

    #[Route('/dashboard/education/{uuid}/delete', name: 'dashboard_education_delete')]
    public function delete(EntityManagerInterface $em, $uuid): Response
    {
        $education = $em->getRepository(Education::class)->findOneBy(['uuid' => $uuid]);
        if (null === $education) {
            return $this->redirectToRoute('dashboard_index');
        }

        // soft delete
        $education->setDeletedAt(new \DateTime());
        $em->persist($education);
        $em->flush();

        // set flash message
        $this->addFlash('success', 'This education has been successfully deleted');

        $listingRoute = $this->getListingRouteByEducationType($education->getType());

        // redirect to listing page
        return $this->redirectToRoute($listingRoute);
    }

    private function getListingRouteByEducationType($type)
    {
        $route = '';
        switch ($type) {
            case Education::TYPE_COURSE:
                $route = 'dashboard_course_index';
                break;
            case Education::TYPE_WORKSHOP:
                $route = 'dashboard_workshop_index';
                break;
            case Education::TYPE_CONVENTION:
                $route = 'dashboard_convention_index';
                break;
            default:
                break;
        }

        return $route;
    }
}
