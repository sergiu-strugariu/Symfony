<?php

namespace App\Controller\Dashboard;

use App\Repository\NpsRepository;
use App\Repository\PacientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NpsController extends AbstractController {

    /**
     * @Route("/dashboard/nps/feedback", name="nps_feedback")
     */
    public function feedback(Request $request, UserRepository $userRepository, PacientRepository $pacientRepository, EntityManagerInterface $em, NpsRepository $npsRepository): Response {
        $params = $request->query->all();
        $error = false;
        $message = '';
        $uuid = '';

        if (empty($params['note']) || empty($params['user_uuid']) || empty($params['pacient_uuid'])) {
            $error = true;
            $message = 'Date invalide!';
        }

        $relation = $userRepository->findOneBy(['uid' => $params['user_uuid']]);
        if (null === $relation) {
            $error = true;
            $message = 'Date invalide!';
        }

        $pacient = $pacientRepository->findOneBy(['uid' => $params['pacient_uuid']]);
        if (null === $pacient) {
            $error = true;
            $message = 'Date invalide!';
        }

        if (!in_array($params['note'], range(1, 10))) {
            $error = true;
            $message = 'Date invalide!';
        }

        $nps = $npsRepository->findOneBy([
            'user' => $relation,
            'pacient' => $pacient
        ]);

        if (null === $nps) {
            $error = true;
            $message = 'Date invalide!';
        }

        if ($nps->hasNote()) {
            $error = true;
            $message = 'Ai selectat deja o nota!';
        } else {
            $nps->setNote($params['note']);
            // write to DB
            $em->persist($nps);
            $em->flush();
            // get uuid
            $uuid = $nps->getUid();
        }

        return $this->render('dashboard/nps/feedback.html.twig', [
                    'error' => $error,
                    'message' => $message,
                    'uuid' => $uuid
        ]);
    }

    /**
     * @Route("/dashboard/nps/{uuid}/ajax/feedback", name="nps_ajax_feedback")
     */
    public function addNpsFeedback(Request $request, NpsRepository $npsRepository, EntityManagerInterface $em, $uuid): Response {
        $nps = $npsRepository->findOneBy(['uid' => $uuid]);
        if (null === $nps) {
            return $this->json([
                        'success' => false,
                        'message' => 'Date invalide'
            ]);
        }

        $feedback = $request->get('feedback');
        if (empty($feedback)) {
            return $this->json([
                        'success' => false,
                        'message' => 'Date invalide'
            ]);
        }

        $nps->setFeedback($feedback);
        // write to DB
        $em->persist($nps);
        $em->flush();

        return $this->json([
                    'success' => true,
                    'message' => 'Multumim pentru feedback!'
        ]);
    }

}
