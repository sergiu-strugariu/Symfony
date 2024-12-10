<?php

namespace App\Controller\Dashboard;

use App\Entity\PacientVisitCalendar;
use App\Repository\NursingHomeRepository;
use App\Repository\PacientVisitCalendarRepository;
use App\Repository\ProspectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class ViewingController extends AbstractController {

    /**
     * @Route("/dashboard/viewing", name="viewing")
     */
    public function viewing(Request $request, ProspectRepository $prospectRepository, NursingHomeRepository $nursingHomeRepository): Response {
        $params = $request->query->all();
        $error = true;
        $message = 'Date invalide!';

        if (empty($params['uuid']) || empty($params['location'])) {
            return $this->render('dashboard/viewing/index.html.twig', [
                        'error' => $error,
                        'message' => $message,
                        'prospect' => null,
                        'uuid' => null,
                        'location' => null,
            ]);
        }

        $prospect = $prospectRepository->findOneBy(['uid' => $params['uuid']]);
        if (null === $prospect) {
            return $this->render('dashboard/viewing/index.html.twig', [
                        'error' => $error,
                        'message' => $message,
                        'prospect' => null,
                        'uuid' => null,
                        'location' => null,
            ]);
        }

        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $params['location']]);
        if (null === $nursingHome) {
            return $this->render('dashboard/viewing/index.html.twig', [
                        'error' => $error,
                        'message' => $message,
                        'prospect' => null,
                        'uuid' => null,
                        'location' => null,
            ]);
        }

        return $this->render('dashboard/viewing/index.html.twig', [
                    'error' => false,
                    'message' => '',
                    'prospect' => $prospect,
                    'uuid' => $params['uuid'],
                    'location' => $params['location']
        ]);
    }

    /**
     * @Route("/dashboard/viewing/{uuid}/ajax/schedule", name="viewing_ajax_schedule")
     */
    public function scheduleViewing(Request $request, ProspectRepository $prospectRepository, NursingHomeRepository $nursingHomeRepository, PacientVisitCalendarRepository $pacientVisitCalendarRepository, EntityManagerInterface $em, $uuid): Response {
        $prospect = $prospectRepository->findOneBy(['uid' => $uuid]);
        if (null === $prospect) {
            return $this->json([
                        'success' => false,
                        'message' => 'Date invalide'
            ]);
        }

        $nursingHome = $nursingHomeRepository->findOneBy(['uid' => $request->get('location')]);
        if (null === $nursingHome) {
            return $this->json([
                        'success' => false,
                        'message' => 'Date invalide'
            ]);
        }

        $date = $request->get('date');
        if (empty($date)) {
            return $this->json([
                        'success' => false,
                        'message' => 'Date invalide'
            ]);
        }

        $scheduledAtStart = \DateTime::createFromFormat('Y-m-d H:i', $date);
        $scheduledAtEnd = clone $scheduledAtStart;
        $tomorrow = new \DateTime('tomorrow');

        if ($scheduledAtStart < $tomorrow) {
            return $this->json([
                        'success' => false,
                        'message' => 'Te rugam sa introduci o data din viitor'
            ]);
        }
        // set scheduled date
        $prospect->setScheduledAt($scheduledAtStart);
        
        // check if visit exists in calendar table
        $visit = $pacientVisitCalendarRepository->findOneBy([
            'prospect' => $prospect
        ]);
        
        // add or update visit to calendar
        if (null === $visit) {
            $visit = new PacientVisitCalendar();
            $visit->setUid(Uuid::v4());
            $visit->setProspect($prospect);
            $visit->setNursingHome($nursingHome);
            $visit->setStatus(PacientVisitCalendar::VISIT_STATUS_PENDING);
            $visit->setObservations('Programare vizionare');
            $visit->setType(PacientVisitCalendar::VISIT_TYPE_VIEWING);
            $visit->setCreatedAt(new \DateTime());
        }
        
        $visit->setStartDate($scheduledAtStart);
        $visit->setEndDate($scheduledAtEnd->modify('+2 hours'));
        if (PacientVisitCalendar::VISIT_STATUS_CANCELED === $visit->getStatus()) {
            $visit->setStatus(PacientVisitCalendar::VISIT_STATUS_PENDING);
        }

        // write to DB
        $em->persist($prospect);
        $em->persist($visit);
        $em->flush();

        return $this->json([
                    'success' => true,
                    'message' => 'Vizita ta a fost programata!'
        ]);
    }

    /**
     * @Route("/dashboard/viewing/{uuid}/ajax/cancel", name="viewing_ajax_cancel")
     */
    public function cancelViewing(Request $request, ProspectRepository $prospectRepository, PacientVisitCalendarRepository $pacientVisitCalendarRepository, EntityManagerInterface $em, $uuid): Response {
        $reason = $request->get('reason');

        if (empty($reason)) {
            return $this->json([
                        'success' => false,
                        'message' => 'Date invalide'
            ]);
        }

        $prospect = $prospectRepository->findOneBy(['uid' => $uuid]);
        if (null === $prospect) {
            return $this->json([
                        'success' => false,
                        'message' => 'Date invalide'
            ]);
        }
        // update prospect
        $prospect->setScheduledAt(null);
        $prospect->setCancelReason($reason);
        $prospect->setCanceled(true);
        // update visit
        $visit = $pacientVisitCalendarRepository->findOneBy([
            'prospect' => $prospect
        ]);
        if (null !== $visit) {
            $visit->setStatus(PacientVisitCalendar::VISIT_STATUS_CANCELED);
            
            $em->persist($visit);
        }
        // write to DB
        $em->persist($prospect);
        $em->flush();

        return $this->json([
                    'success' => true,
                    'message' => 'Vizita ta a fost anulata!'
        ]);
    }

}
