<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'An education with this slug already exists.', errorPath: 'title')]
class Article
{
    const STATUS_PUBLISHED = 'published';
    const STATUS_DRAFT = 'draft';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 100)]
    private ?string $imageName = null;

    #[ORM\Column(length: 40)]
    private ?string $status = self::STATUS_PUBLISHED;

    #[ORM\OneToMany(targetEntity: ArticleTranslation::class, mappedBy: 'article', orphanRemoval: true)]
    private Collection $articleTranslations;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;
    
    private $defaultLocale = 'ro';

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->articleTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
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

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

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
     * @return Collection<int, ArticleRepository>
     */
    public function getArticleTranslations(): Collection
    {
        return $this->articleTranslations;
    }

    public function addArticleTranslation(ArticleTranslation $articleTranslation): static
    {
        if (!$this->articleTranslations->contains($articleTranslation)) {
            $this->articleTranslations->add($articleTranslation);
            $articleTranslation->setArticle($this);
        }

        return $this;
    }

    public function removeEducationTranslation(ArticleTranslation $articleTranslation): static
    {
        if ($this->articleTranslations->removeElement($articleTranslation)) {
            // set the owning side to null (unless already changed)
            if ($articleTranslation->getArticle() === $this) {
                $articleTranslation->setArticle(null);
            }
        }

        return $this;
    }

    public function getTranslation($locale)
    {
        $translations = $this->getArticleTranslations()->filter(function ($articleTranslation) use ($locale) {
            return $articleTranslation->getLanguage()->getLocale() === $locale;
        });
        
        if ($translations->isEmpty()) {
            return $this->getArticleTranslations()->filter(function ($articleTranslation) {
                return $articleTranslation->getLanguage()->getLocale() === $this->defaultLocale;
            })->first();
        }
        
        return $translations->first();
    }
}
