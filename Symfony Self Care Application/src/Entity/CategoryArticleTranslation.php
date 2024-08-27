<?php

namespace App\Entity;

use App\Repository\CategoryArticleTranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryArticleTranslationRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_category_article_language', columns: ['category_article_id', 'language_id'])]
class CategoryArticleTranslation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'categoryArticleTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CategoryArticle $categoryArticle = null;

    #[ORM\ManyToOne(inversedBy: 'categoryArticleTranslations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryArticle(): ?CategoryArticle
    {
        return $this->categoryArticle;
    }

    public function setCategoryArticle(?CategoryArticle $categoryArticle): static
    {
        $this->categoryArticle = $categoryArticle;

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
