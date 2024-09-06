<?php

namespace App\Entity;

use App\Repository\CategoryJobTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryJobTranslationRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_category_job_language', columns: ['category_job_id', 'language_id'])]
class CategoryJobTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'categoryJobTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategoryJob $categoryJob = null;

    #[ORM\ManyToOne(inversedBy: 'categoryJobTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryJob(): ?CategoryJob
    {
        return $this->categoryJob;
    }

    public function setCategoryJob(?CategoryJob $categoryJob): static
    {
        $this->categoryJob = $categoryJob;

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

    /**
     * @return string|null
     */
    public function getLanguageLocale(): ?string
    {
        return $this->language->getLocale();
    }
}
