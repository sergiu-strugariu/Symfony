<?php

namespace App\Entity;

use App\Repository\FeedbackAnswerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedbackAnswerRepository::class)]
class FeedbackAnswer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\ManyToOne(inversedBy: 'feedbackAnswers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Feedback $feedback = null;
    
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?FeedbackQuestion $question = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $answer = null;

    public function getFeedback(): ?Feedback
    {
        return $this->feedback;
    }

    public function setFeedback(?Feedback $feedback): static
    {
        $this->feedback = $feedback;

        return $this;
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getQuestion(): ?FeedbackQuestion
    {
        return $this->question;
    }

    public function setQuestion(?FeedbackQuestion $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): static
    {
        $this->answer = $answer;

        return $this;
    }
    
}
