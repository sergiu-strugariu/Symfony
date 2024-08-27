<?php

namespace App\Entity;

use App\Repository\CategoryArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CategoryArticleRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'A category with this name already exists.', errorPath: 'title')]
class CategoryArticle
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, CategoryArticleTranslation>
     */
    #[ORM\OneToMany(targetEntity: CategoryArticleTranslation::class, mappedBy: 'categoryArticle', orphanRemoval: true)]
    private Collection $categoryArticleTranslations;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\ManyToMany(targetEntity: Article::class, mappedBy: 'categoryArticles')]
    private Collection $articles;

    public function __construct()
    {
        $this->categoryArticleTranslations = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->articles = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

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

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return Collection<int, CategoryArticleTranslation>
     */
    public function getCategoryArticleTranslations(): Collection
    {
        return $this->categoryArticleTranslations;
    }

    public function addCategoryArticleTranslation(CategoryArticleTranslation $categoryArticleTranslation): static
    {
        if (!$this->categoryArticleTranslations->contains($categoryArticleTranslation)) {
            $this->categoryArticleTranslations->add($categoryArticleTranslation);
            $categoryArticleTranslation->setCategoryArticle($this);
        }

        return $this;
    }

    public function removeCategoryArticleTranslation(CategoryArticleTranslation $categoryArticleTranslation): static
    {
        if ($this->categoryArticleTranslations->removeElement($categoryArticleTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryArticleTranslation->getCategoryArticle() === $this) {
                $categoryArticleTranslation->setCategoryArticle(null);
            }
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => self::STATUS_DRAFT,
            self::STATUS_PUBLISHED => self::STATUS_PUBLISHED
        ];
    }

    /**
     * @param $locale
     * @return CategoryArticleTranslation|null
     */
    public function getTranslation($locale): ?CategoryArticleTranslation
    {
        foreach ($this->categoryArticleTranslations as $translation) {
            if ($translation->getLanguage()->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->addCategoryArticle($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): static
    {
        if ($this->articles->removeElement($article)) {
            $article->removeCategoryArticle($this);
        }

        return $this;
    }
}
