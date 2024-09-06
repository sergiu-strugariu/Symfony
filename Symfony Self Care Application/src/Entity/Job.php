<?php

namespace App\Entity;

use App\Repository\JobRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: JobRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'A job with this title already exists.', errorPath: 'title')]
class Job
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    const TYPE_PART_TYME = 'Part-Time';
    const TYPE_FULL_TYME = 'Full-Time';

    const ENTITY_NAME = 'job';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'jobs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'jobs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne(inversedBy: 'jobs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?County $county = null;

    #[ORM\ManyToOne(inversedBy: 'jobs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?City $city = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 99)]
    private ?string $jobType = null;

    #[ORM\Column(length: 199)]
    private ?string $address = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    private ?string $startGrossSalary = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    private ?string $endGrossSalary = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fileName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, JobTranslation>
     */
    #[ORM\OneToMany(targetEntity: JobTranslation::class, mappedBy: 'job', orphanRemoval: true)]
    private Collection $jobTranslations;

    /**
     * @var Collection<int, CategoryJob>
     */
    #[ORM\ManyToMany(targetEntity: CategoryJob::class, inversedBy: 'jobs')]
    #[ORM\JoinTable(name: 'job_has_category')]
    private Collection $categoryJobs;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->jobTranslations = new ArrayCollection();
        $this->categoryJobs = new ArrayCollection();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    public function getJobType(): ?string
    {
        return $this->jobType;
    }

    public function setJobType(string $jobType): static
    {
        $this->jobType = $jobType;

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

    public function getStartGrossSalary(): ?string
    {
        return $this->startGrossSalary;
    }

    public function setStartGrossSalary(int $startGrossSalary): static
    {
        $this->startGrossSalary = $startGrossSalary;

        return $this;
    }

    public function getEndGrossSalary(): ?string
    {
        return $this->endGrossSalary;
    }

    public function setEndGrossSalary(int $endGrossSalary): static
    {
        $this->endGrossSalary = $endGrossSalary;

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
     * @return Collection<int, JobTranslation>
     */
    public function getJobTranslations(): Collection
    {
        return $this->jobTranslations;
    }

    public function addJobTranslation(JobTranslation $jobTranslation): static
    {
        if (!$this->jobTranslations->contains($jobTranslation)) {
            $this->jobTranslations->add($jobTranslation);
            $jobTranslation->setJob($this);
        }

        return $this;
    }

    public function removeJobTranslation(JobTranslation $jobTranslation): static
    {
        if ($this->jobTranslations->removeElement($jobTranslation)) {
            // set the owning side to null (unless already changed)
            if ($jobTranslation->getJob() === $this) {
                $jobTranslation->setJob(null);
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
    public static function getJobTypes(): array
    {
        return [
            self::TYPE_PART_TYME => self::TYPE_PART_TYME,
            self::TYPE_FULL_TYME => self::TYPE_FULL_TYME
        ];
    }

    /**
     * @return string[]
     */
    public static function getBenefits(): array
    {
        return [
            "6 săptămini de concediu de odihnă plătit",
            "Program de asistență pentru angajați pentru a vă sprijini sănătatea mintală",
            "Cursuri extinse de formare profesională",
            "Analize medicale anuale gratuite",
            "Tichete de masă",
            "Bonusuri anuale"
        ];
    }

    public function getTranslation($locale): ?JobTranslation
    {
        foreach ($this->jobTranslations as $translation) {
            if ($translation->getLanguage()->getLocale() === $locale) {
                return $translation;
            }
        }

        return null;
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

        $categories = $this->categoryJobs->matching($criteria);

        return $categories->isEmpty() ? null : $categories->first();
    }

    /**
     * @return Collection<int, CategoryJob>
     */
    public function getCategoryJobs(): Collection
    {
        return $this->categoryJobs;
    }

    public function addCategoryJob(CategoryJob $categoryJob): static
    {
        if (!$this->categoryJobs->contains($categoryJob)) {
            $this->categoryJobs->add($categoryJob);
        }

        return $this;
    }

    public function removeCategoryJob(CategoryJob $categoryJob): static
    {
        $this->categoryJobs->removeElement($categoryJob);

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
}
