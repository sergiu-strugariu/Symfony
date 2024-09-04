<?php

namespace App\Entity;

use App\Repository\TrainingCourseRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TrainingCourseRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'A course with this name already exists.', errorPath: 'title')]
class TrainingCourse
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    const FORMAT_PHYSICAL = 'physical';
    const FORMAT_ONLINE = 'online';

    const ENTITY_NAME = 'course';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'trainingCourses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'trainingCourses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'trainingCourses')]
    private ?County $county = null;

    #[ORM\ManyToOne(inversedBy: 'trainingCourses')]
    private ?City $city = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(length: 100, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 199)]
    private ?string $address = null;

    #[ORM\Column(length: 199, nullable: true)]
    private ?string $fileName = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(length: 99)]
    private ?string $format = null;

    #[ORM\Column]
    private ?int $minParticipant = null;

    #[ORM\Column]
    private ?int $maxParticipant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startCourseDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, TrainingCourseTranslation>
     */
    #[ORM\OneToMany(targetEntity: TrainingCourseTranslation::class, mappedBy: 'trainingCourse', orphanRemoval: true)]
    private Collection $trainingCourseTranslations;

    /**
     * @var Collection<int, CategoryCourse>
     */
    #[ORM\ManyToMany(targetEntity: CategoryCourse::class, inversedBy: 'trainingCourses')]
    #[ORM\JoinTable(name: 'training_has_category')]
    private Collection $categoryCourses;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->trainingCourseTranslations = new ArrayCollection();
        $this->categoryCourses = new ArrayCollection();
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

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
     * @return Collection<int, TrainingCourseTranslation>
     */
    public function getTrainingCourseTranslations(): Collection
    {
        return $this->trainingCourseTranslations;
    }

    public function addTrainingCourseTranslation(TrainingCourseTranslation $trainingCourseTranslation): static
    {
        if (!$this->trainingCourseTranslations->contains($trainingCourseTranslation)) {
            $this->trainingCourseTranslations->add($trainingCourseTranslation);
            $trainingCourseTranslation->setTrainingCourse($this);
        }

        return $this;
    }

    public function removeTrainingCourseTranslation(TrainingCourseTranslation $trainingCourseTranslation): static
    {
        if ($this->trainingCourseTranslations->removeElement($trainingCourseTranslation)) {
            // set the owning side to null (unless already changed)
            if ($trainingCourseTranslation->getTrainingCourse() === $this) {
                $trainingCourseTranslation->setTrainingCourse(null);
            }
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => self::STATUS_DRAFT,
            self::STATUS_PUBLISHED => self::STATUS_PUBLISHED
        ];
    }

    /**
     * @return string[]
     */
    public static function getFormats(): array
    {
        return [
            self::FORMAT_PHYSICAL => self::FORMAT_PHYSICAL,
            self::FORMAT_ONLINE => self::FORMAT_ONLINE
        ];
    }

    public function getTranslation($locale): ?TrainingCourseTranslation
    {
        foreach ($this->trainingCourseTranslations as $translation) {
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
     * @return string
     */
    public function getCountyName(): string
    {
        return $this->getCounty()->getName();
    }

    /**
     * @return string
     */
    public function getCityName(): string
    {
        return $this->getCity()->getName();
    }

    /**
     * @return CompanyReview|false|mixed|null
     */
    public function getFirstCategory(): mixed
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('status', self::STATUS_PUBLISHED))
            ->orderBy(['createdAt' => 'DESC'])
            ->setMaxResults(1);

        $reviews = $this->categoryCourses->matching($criteria);

        return $reviews->isEmpty() ? null : $reviews->first()->getTranslation('ro')->getTitle();
    }

    /**
     * @return Collection<int, CategoryCourse>
     */
    public function getCategoryCourses(): Collection
    {
        return $this->categoryCourses;
    }

    public function addCategoryCourse(CategoryCourse $categoryCourse): static
    {
        if (!$this->categoryCourses->contains($categoryCourse)) {
            $this->categoryCourses->add($categoryCourse);
        }

        return $this;
    }

    public function removeCategoryCourse(CategoryCourse $categoryCourse): static
    {
        $this->categoryCourses->removeElement($categoryCourse);

        return $this;
    }

    public function getCounty(): ?County
    {
        return $this->county;
    }

    public function setCounty(?County $county): static
    {
        $this->county = $county;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getStartCourseDate(): ?\DateTimeInterface
    {
        return $this->startCourseDate;
    }

    public function setStartCourseDate(\DateTimeInterface $startCourseDate): static
    {
        $this->startCourseDate = $startCourseDate;

        return $this;
    }

    public function getMinParticipant(): ?int
    {
        return $this->minParticipant;
    }

    public function setMinParticipant(int $minParticipant): static
    {
        $this->minParticipant = $minParticipant;

        return $this;
    }

    public function getMaxParticipant(): ?int
    {
        return $this->maxParticipant;
    }

    public function setMaxParticipant(int $maxParticipant): static
    {
        $this->maxParticipant = $maxParticipant;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): static
    {
        $this->format = $format;

        return $this;
    }
}
