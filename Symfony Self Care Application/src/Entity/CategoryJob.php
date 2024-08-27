<?php

namespace App\Entity;

use App\Repository\CategoryJobRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CategoryJobRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'A category with this name already exists.', errorPath: 'title')]
class CategoryJob
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
     * @var Collection<int, CategoryJobTranslation>
     */
    #[ORM\OneToMany(targetEntity: CategoryJobTranslation::class, mappedBy: 'categoryJob', orphanRemoval: true)]
    private Collection $categoryJobTranslations;

    /**
     * @var Collection<int, Job>
     */
    #[ORM\ManyToMany(targetEntity: Job::class, mappedBy: 'categoryJobs')]
    private Collection $jobs;

    public function __construct()
    {
        $this->categoryJobTranslations = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->jobs = new ArrayCollection();
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
     * @return Collection<int, CategoryJobTranslation>
     */
    public function getCategoryJobTranslations(): Collection
    {
        return $this->categoryJobTranslations;
    }

    public function addCategoryJobTranslation(CategoryJobTranslation $categoryJobTranslation): static
    {
        if (!$this->categoryJobTranslations->contains($categoryJobTranslation)) {
            $this->categoryJobTranslations->add($categoryJobTranslation);
            $categoryJobTranslation->setCategoryJob($this);
        }

        return $this;
    }

    public function removeCategoryJobTranslation(CategoryJobTranslation $categoryJobTranslation): static
    {
        if ($this->categoryJobTranslations->removeElement($categoryJobTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryJobTranslation->getCategoryJob() === $this) {
                $categoryJobTranslation->setCategoryJob(null);
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

    public function getTranslation($locale): ?CategoryJobTranslation
    {
        foreach ($this->categoryJobTranslations as $translation) {
            if ($translation->getLanguage()->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, Job>
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function addJob(Job $job): static
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs->add($job);
            $job->addCategoryJob($this);
        }

        return $this;
    }

    public function removeJob(Job $job): static
    {
        if ($this->jobs->removeElement($job)) {
            $job->removeCategoryJob($this);
        }

        return $this;
    }
}
