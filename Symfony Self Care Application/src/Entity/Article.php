<?php

namespace App\Entity;

use App\Helper\DefaultHelper;
use App\Repository\ArticleRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'A article with this name already exists.', errorPath: 'title')]
class Article
{
    const ENTITY_NAME = 'article';
    const ENTITY_AI_NAME = 'article-ai';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fileName = null;

    #[ORM\Column]
    private ?bool $isGenerated = null;

    /**
     * @var Collection<int, ArticleTranslation>
     */
    #[ORM\OneToMany(targetEntity: ArticleTranslation::class, mappedBy: 'article', orphanRemoval: true)]
    private Collection $articleTranslations;

    /**
     * @var Collection<int, CategoryArticle>
     */
    #[ORM\ManyToMany(targetEntity: CategoryArticle::class, inversedBy: 'articles')]
    #[ORM\JoinTable(name: 'article_has_category')]
    private Collection $categoryArticles;

    /**
     * @var Collection<int, EntityDisplayLog>
     */
    #[ORM\OneToMany(targetEntity: EntityDisplayLog::class, mappedBy: 'article', orphanRemoval: true)]
    private Collection $entityDisplayLogs;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->status = DefaultHelper::STATUS_DRAFT;
        $this->articleTranslations = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->isGenerated = false;
        $this->categoryArticles = new ArrayCollection();
        $this->entityDisplayLogs = new ArrayCollection();
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

    public function isGenerated(): ?bool
    {
        return $this->isGenerated;
    }

    public function setGenerated(bool $isGenerated): static
    {
        $this->isGenerated = $isGenerated;

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

    public function getFileName(): ?string
    {
        return $this->fileName ?: 'default.png';
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

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

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(\DateTimeInterface $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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
     * @return Collection<int, ArticleTranslation>
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

    public function removeArticleTranslation(ArticleTranslation $articleTranslation): static
    {
        if ($this->articleTranslations->removeElement($articleTranslation)) {
            // set the owning side to null (unless already changed)
            if ($articleTranslation->getArticle() === $this) {
                $articleTranslation->setArticle(null);
            }
        }

        return $this;
    }

    public function getTranslation($locale): ?ArticleTranslation
    {
        foreach ($this->articleTranslations as $translation) {
            if ($translation->getLanguage()->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, CategoryArticle>
     */
    public function getCategoryArticles(): Collection
    {
        return $this->categoryArticles;
    }

    public function addCategoryArticle(CategoryArticle $categoryArticle): static
    {
        if (!$this->categoryArticles->contains($categoryArticle)) {
            $this->categoryArticles->add($categoryArticle);
        }

        return $this;
    }

    public function removeCategoryArticle(CategoryArticle $categoryArticle): static
    {
        $this->categoryArticles->removeElement($categoryArticle);

        return $this;
    }

    /**
     * @return Collection<int, EntityDisplayLog>
     */
    public function getEntityDisplayLogs(): Collection
    {
        return $this->entityDisplayLogs;
    }

    public function addEntityDisplayLog(EntityDisplayLog $entityDisplayLog): static
    {
        if (!$this->entityDisplayLogs->contains($entityDisplayLog)) {
            $this->entityDisplayLogs->add($entityDisplayLog);
            $entityDisplayLog->setArticle($this);
        }

        return $this;
    }

    public function removeEntityDisplayLog(EntityDisplayLog $entityDisplayLog): static
    {
        if ($this->entityDisplayLogs->removeElement($entityDisplayLog)) {
            // set the owning side to null (unless already changed)
            if ($entityDisplayLog->getArticle() === $this) {
                $entityDisplayLog->setArticle(null);
            }
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMembershipPrice(): ?string
    {
        return $this->getUser()->getMembershipPackage()->getPrice();
    }
}
