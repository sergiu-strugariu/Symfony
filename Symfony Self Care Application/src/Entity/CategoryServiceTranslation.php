<?php

namespace App\Entity;

use App\Repository\CategoryServiceTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryServiceTranslationRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_category_service_language', columns: ['category_service_id', 'language_id'])]
class CategoryServiceTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'categoryServiceTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategoryService $categoryService = null;

    #[ORM\ManyToOne(inversedBy: 'categoryServiceTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryService(): ?CategoryService
    {
        return $this->categoryService;
    }

    public function setCategoryService(?CategoryService $categoryService): static
    {
        $this->categoryService = $categoryService;

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
