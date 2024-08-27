<?php

namespace App\Entity;

use App\Repository\EducationScheduleTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EducationScheduleTranslationRepository::class)]
class EducationScheduleTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'educationScheduleTranslation')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EducationSchedule $educationSchedule = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEducationSchedule(): EducationSchedule
    {
        return $this->educationSchedule;
    }

    public function setEducationSchedule(?EducationSchedule $educationSchedule): static
    {
        $this->educationSchedule = $educationSchedule;

        return $this;
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
}
