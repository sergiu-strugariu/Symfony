<?php

namespace App\DataFixtures;

use App\Entity\FeedbackQuestion;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class FeedbackQuestionFixtures extends Fixture
{
    
    const QUESTIONS = [
        'La ce curs/workshop ați participat și în ce perioadă?',
        'V-au fost de folos informațiile oferite de către echipa organizatorică (e-mail, comunicare, detalii check – in, etc)?',
        'Ați avut anterior cunoștințe din domeniul cursului/workshopului? Dacă da, v-au fost de folos în timpul educației?',
        'Ați fost informat despre metodele de evaluare, examinare și reexaminare?',
        'Considerați că modalitatea de livrare a cursului/workshop-ului a fost conform așteptărilor dvs?',
        'Cum a decurs interacțiunea cu profesorii?',
        'În ce mod v-a fost de folos feedback-ul primit din partea profesorilor în perioada educației?',
        'Ați primit, din partea profesorilor, suport suficient pentru îndeplinirea cerințelor?',
        'Ați recomanda acest curs/workshop?  ',
        'Ce alte workshop-uri ați fi interesat să organizăm pentru dezvoltarea dvs. in fitness?'
    ];
    
    public function load(ObjectManager $manager): void
    {
        $questions = self::QUESTIONS;
        foreach ($questions as $order => $question) {
            $feedbackQuestion = new FeedbackQuestion();
            $feedbackQuestion->setTitle($question);
            $feedbackQuestion->setSortOrder($order);
            $manager->persist($feedbackQuestion);
        }

        $manager->flush();
    }
}
