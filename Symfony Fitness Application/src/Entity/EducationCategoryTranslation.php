<?php

namespace App\Entity;

use App\Repository\EducationCategoryTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EducationCategoryTranslationRepository::class)]
class EducationCategoryTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'educationCategoryTranslations')]
    private ?EducationCategory $educationCategory = null;

    #[ORM\ManyToOne(inversedBy: 'educationCategoryTranslations')]
    private ?Language $language = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEducationCategory(): ?EducationCategory
    {
        return $this->educationCategory;
    }

    public function setEducationCategory(?EducationCategory $educationCategory): static
    {
        $this->educationCategory = $educationCategory;

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
