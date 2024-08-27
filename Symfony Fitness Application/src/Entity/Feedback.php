<?php

namespace App\Entity;

use App\Repository\FeedbackRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FeedbackRepository::class)]
class Feedback
{
    
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 40)]
    private ?string $uuid = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Education $education = null;

    #[ORM\ManyToOne]
    private ?User $user = null;
    
    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $answeredAt = null;

    /**
     * @var Collection<int, FeedbackAnswer>
     */
    #[ORM\OneToMany(targetEntity: FeedbackAnswer::class, mappedBy: 'feedback', orphanRemoval: true)]
    private Collection $feedbackAnswers;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->feedbackAnswers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getEducation(): ?Education
    {
        return $this->education;
    }

    public function setEducation(?Education $education): static
    {
        $this->education = $education;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
    
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
    
    public function getAnsweredAt(): ?\DateTimeInterface
    {
        return $this->answeredAt;
    }

    public function setAnsweredAt(?\DateTimeInterface $answeredAt): static
    {
        $this->answeredAt = $answeredAt;

        return $this;
    }

    /**
     * @return Collection<int, FeedbackAnswer>
     */
    public function getFeedbackAnswers(): Collection
    {
        return $this->feedbackAnswers;
    }

    public function addFeedbackAnswer(FeedbackAnswer $feedbackAnswer): static
    {
        if (!$this->feedbackAnswers->contains($feedbackAnswer)) {
            $this->feedbackAnswers->add($feedbackAnswer);
            $feedbackAnswer->setFeedback($this);
        }

        return $this;
    }

    public function removeFeedbackAnswer(FeedbackAnswer $feedbackAnswer): static
    {
        if ($this->feedbackAnswers->removeElement($feedbackAnswer)) {
            // set the owning side to null (unless already changed)
            if ($feedbackAnswer->getFeedback() === $this) {
                $feedbackAnswer->setFeedback(null);
            }
        }

        return $this;
    }
}
