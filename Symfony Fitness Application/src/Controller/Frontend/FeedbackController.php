<?php

namespace App\Controller\Frontend;

use App\Entity\Feedback;
use App\Entity\FeedbackAnswer;
use App\Repository\FeedbackRepository;
use App\Repository\FeedbackQuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class FeedbackController extends AbstractController
{
    #[Route('/feedback/{uuid}', name: 'app_feedback')]
    public function index(Request $request, EntityManagerInterface $em, FeedbackRepository $feedbackRepository, FeedbackQuestionRepository $feedbackQuestionRepository, $uuid): Response {
        $feedback = $feedbackRepository->findOneBy(['uuid' => $uuid]);

        if (null === $feedback) {
            return $this->redirectToRoute('app_index');
        }
        
        if ($feedback->getStatus() === Feedback::STATUS_COMPLETED) {
            return $this->redirectToRoute('app_index');
        }
        
        $feedbackQuestions = $feedbackQuestionRepository->findBy([], ['sortOrder' => 'ASC']);
        
        if ($request->isMethod('POST')) {
            $answers = $request->get('answers');
            foreach ($answers as $questionId => $answer) {
                $feedbackQuestion = $feedbackQuestionRepository->find($questionId);
                if (null === $feedbackQuestion) {
                    continue;
                }
                
                $feedbackAnswer = new FeedbackAnswer();
                $feedbackAnswer->setFeedback($feedback);
                $feedbackAnswer->setQuestion($feedbackQuestion);
                $feedbackAnswer->setAnswer($answer);
                
                $em->persist($feedbackAnswer);
                $em->flush();
            }
            
            $feedback->setStatus(Feedback::STATUS_COMPLETED);
            $feedback->setAnsweredAt(new \DateTime());
            $em->persist($feedback);
            $em->flush();
        }
        
        return $this->render('frontend/feedback/index.html.twig', [
                    'feedback' => $feedback,
                    'questions' => $feedbackQuestions
        ]);
    }

}
