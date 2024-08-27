<?php

namespace App\Entity;

use App\Repository\TrainingCourseTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrainingCourseTranslationRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_training_course_language', columns: ['training_course_id', 'language_id'])]
class TrainingCourseTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'trainingCourseTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TrainingCourse $trainingCourse = null;

    #[ORM\ManyToOne(inversedBy: 'trainingCourseTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 99)]
    private ?string $duration = null;

    #[ORM\Column(length: 99)]
    private ?string $level = null;

    #[ORM\Column(length: 99)]
    private ?string $certificate = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $shortDescription = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $body = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrainingCourse(): ?TrainingCourse
    {
        return $this->trainingCourse;
    }

    public function setTrainingCourse(?TrainingCourse $trainingCourse): static
    {
        $this->trainingCourse = $trainingCourse;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguageLocale(): ?string
    {
        return $this->language->getLocale();
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getCertificate(): ?string
    {
        return $this->certificate;
    }

    public function setCertificate(string $certificate): static
    {
        $this->certificate = $certificate;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): static
    {
        $this->body = $body;

        return $this;
    }
}
