<?php

namespace App\Entity;

use App\Repository\CategoryCourseRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CategoryCourseRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'A category with this name already exists.', errorPath: 'title')]
class CategoryCourse
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
     * @var Collection<int, CategoryCourseTranslation>
     */
    #[ORM\OneToMany(targetEntity: CategoryCourseTranslation::class, mappedBy: 'categoryCourse', orphanRemoval: true)]
    private Collection $categoryCourseTranslations;

    /**
     * @var Collection<int, TrainingCourse>
     */
    #[ORM\ManyToMany(targetEntity: TrainingCourse::class, mappedBy: 'categoryCourses')]
    private Collection $trainingCourses;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->categoryCourseTranslations = new ArrayCollection();
        $this->trainingCourses = new ArrayCollection();
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
     * @return Collection<int, CategoryCourseTranslation>
     */
    public function getCategoryCourseTranslations(): Collection
    {
        return $this->categoryCourseTranslations;
    }

    public function addCategoryCourseTranslation(CategoryCourseTranslation $categoryCourseTranslation): static
    {
        if (!$this->categoryCourseTranslations->contains($categoryCourseTranslation)) {
            $this->categoryCourseTranslations->add($categoryCourseTranslation);
            $categoryCourseTranslation->setCategoryCourse($this);
        }

        return $this;
    }

    public function removeCategoryCourseTranslation(CategoryCourseTranslation $categoryCourseTranslation): static
    {
        if ($this->categoryCourseTranslations->removeElement($categoryCourseTranslation)) {
            // set the owning side to null (unless already changed)
            if ($categoryCourseTranslation->getCategoryCourse() === $this) {
                $categoryCourseTranslation->setCategoryCourse(null);
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

    public function getTranslation($locale): ?CategoryCourseTranslation
    {
        foreach ($this->categoryCourseTranslations as $translation) {
            if ($translation->getLanguage()->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, TrainingCourse>
     */
    public function getTrainingCourses(): Collection
    {
        return $this->trainingCourses;
    }

    public function addTrainingCourse(TrainingCourse $trainingCourse): static
    {
        if (!$this->trainingCourses->contains($trainingCourse)) {
            $this->trainingCourses->add($trainingCourse);
            $trainingCourse->addCategoryCourse($this);
        }

        return $this;
    }

    public function removeTrainingCourse(TrainingCourse $trainingCourse): static
    {
        if ($this->trainingCourses->removeElement($trainingCourse)) {
            $trainingCourse->removeCategoryCourse($this);
        }

        return $this;
    }
}
