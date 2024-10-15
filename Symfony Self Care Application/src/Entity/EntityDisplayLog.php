<?php

namespace App\Entity;

use App\Repository\EntityDisplayLogRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntityDisplayLogRepository::class)]
class EntityDisplayLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $entity = null;

    #[ORM\ManyToOne(inversedBy: 'entityDisplayLogs')]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'entityDisplayLogs')]
    private ?Article $article = null;

    #[ORM\ManyToOne(inversedBy: 'entityDisplayLogs')]
    private ?Job $job = null;

    #[ORM\ManyToOne(inversedBy: 'entityDisplayLogs')]
    private ?TrainingCourse $course = null;

    #[ORM\Column]
    private ?int $displayCount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $displayedAt = null;

    public function __construct()
    {
        $this->displayedAt = new DateTime();
        $this->displayCount = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): static
    {
        $this->job = $job;

        return $this;
    }

    public function getCourse(): ?TrainingCourse
    {
        return $this->course;
    }

    public function setCourse(?TrainingCourse $course): static
    {
        $this->course = $course;

        return $this;
    }

    public function getDisplayCount(): ?int
    {
        return $this->displayCount;
    }

    public function setDisplayCount(int $displayCount): static
    {
        $this->displayCount = $displayCount;

        return $this;
    }

    public function incrementDisplayCount(): static
    {
        $this->displayCount += 1;

        return $this;
    }

    public function getDisplayedAt(): ?\DateTimeInterface
    {
        return $this->displayedAt;
    }

    public function setDisplayedAt(\DateTimeInterface $displayedAt): static
    {
        $this->displayedAt = $displayedAt;

        return $this;
    }
}
