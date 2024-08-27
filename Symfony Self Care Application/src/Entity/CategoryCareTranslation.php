<?php

namespace App\Entity;

use App\Repository\CategoryCareTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryCareTranslationRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_category_care_language', columns: ['category_care_id', 'language_id'])]
class CategoryCareTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'categoryCareTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategoryCare $categoryCare = null;

    #[ORM\ManyToOne(inversedBy: 'categoryCareTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryCare(): ?CategoryCare
    {
        return $this->categoryCare;
    }

    public function setCategoryCare(?CategoryCare $categoryCare): static
    {
        $this->categoryCare = $categoryCare;

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
