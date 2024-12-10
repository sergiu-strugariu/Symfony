<?php

namespace App\Controller\Dashboard;

use App\Entity\Summary;
use App\Entity\SummaryType;
use App\Entity\User;
use App\Repository\SummaryNotificationRepository;
use App\Repository\SummaryPacientRepository;
use App\Repository\SummaryRepository;
use App\Repository\SummaryTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class SummaryController extends AbstractController
{
    /**
     * @Route("/dashboard/summaries", name="dashboard_summaries")
     */
    public function index(SummaryRepository $summaryRepository, SummaryTypeRepository $summaryTypeRepository, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $role = $user->getRole();
        $today = new \DateTime();

        $summary = $summaryRepository->findOneBy([
            'user' => $user,
            'summaryDate' => $today
        ]);

        $summaryTypesByRole = [
            'ROLE_MEDIC' => SummaryType::SUMMARY_TYPE_MEDIC,
            'ROLE_PHYSICAL_THERAPY' => SummaryType::SUMMARY_TYPE_PHYSICAL_THERAPY,
            'ROLE_PSYCHOTHERAPY' => SummaryType::SUMMARY_TYPE_PSYCHOTHERAPY,
            'ROLE_SOCIAL_WORKER' => SummaryType::SUMMARY_TYPE_SOCIAL_WORKER,
            'ROLE_RECEPTION' => SummaryType::SUMMARY_TYPE_RECEPTION,
            'ROLE_ASSISTANCE_MEDICAL' => SummaryType::SUMMARY_TYPE_ASSISTANCE_MEDICAL,
            'ROLE_ASSISTANCE_MEDICAL_PHARMACY' => SummaryType::SUMMARY_TYPE_ASSISTANCE_MEDICAL,
            'ROLE_ORDERLY' => SummaryType::SUMMARY_TYPE_ORDERLY,
            'ROLE_ADMIN' => SummaryType::SUMMARY_TYPE_COOK,
            'ROLE_MANAGEMENT' => SummaryType::SUMMARY_TYPE_COOK
        ];

        if (null === $summary) {
            if (isset($summaryTypesByRole[$role])) {
                $summary = new Summary();
                $summary->setUser($user);
                $summary->setSummaryDate($today);
                $summary->setUid(Uuid::v4());
                $summaryType = $summaryTypeRepository->find($summaryTypesByRole[$role]);
                $summary->setType($summaryType);

                $em->persist($summary);
                $em->flush();
            }
        }

        $summaries = $summaryRepository->findBy(['user' => $user], ['summaryDate' => 'DESC']);

        return $this->render('dashboard/summary/index.html.twig', [
            'summaries' => $summaries
        ]);
    }

    /**
     * @Route("/dashboard/summary/{uuid}", name="dashboard_summary")
     */
    public function summary(SummaryRepository $summaryRepository, SummaryPacientRepository $summaryPacientRepository, SummaryNotificationRepository $summaryNotificationRepository, $uuid): Response
    {
        $summary = $summaryRepository->findOneBy([
            'uid' => $uuid
        ]);

        if (null === $summary) {
            return $this->redirectToRoute('dashboard_summaries');
        }

        $summaries = $summaryPacientRepository->findBy([
            'summary' => $summary
        ]);
        $loggedUser = $this->getUser();
        $template = 'dashboard/summary/summary.html.twig';
        $role = $this->getUser()->getRole();

        switch ($role) {
            case 'ROLE_ASSISTANCE_MEDICAL':
            case 'ROLE_ASSISTANCE_MEDICAL_PHARMACY':
                $template = 'dashboard/summary/summary_assistance_medical.html.twig';
                break;
            case 'ROLE_PHYSICAL_THERAPY':
                $template = 'dashboard/summary/summary_physical_therapy.html.twig';
                break;
            case 'ROLE_ORDERLY':
                $template = 'dashboard/summary/summary_orderly.html.twig';
                break;
            case 'ROLE_ADMIN':
            case 'ROLE_MANAGEMENT':
                $template = 'dashboard/summary/summary_cook.html.twig';
                break;
            default:
                break;
        }

        $date = (new \DateTime())->format('Y-m-d');
        $summaryNotifications = $summaryNotificationRepository->findSummaryNotificationsByUser($loggedUser, $date);

        return $this->render($template, [
            'summary' => $summary,
            'summaries' => $summaries,
            'summaryNotifications' => $summaryNotifications
        ]);
    }

}
