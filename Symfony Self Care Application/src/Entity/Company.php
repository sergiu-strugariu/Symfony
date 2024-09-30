<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'A company with this name already exists.', errorPath: 'name')]
class Company
{
    const LOCATION_TYPE_CARE = 'care';
    const LOCATION_TYPE_PROVIDER = 'provider';

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'companies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'companies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?County $county = null;

    #[ORM\ManyToOne(inversedBy: 'companies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?City $city = null;

    #[ORM\Column(length: 99)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 199)]
    private ?string $address = null;

    #[ORM\Column(length: 40)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 99)]
    private ?string $locationType = null;

    #[ORM\Column(length: 20)]
    private ?string $companyType = null;

    #[ORM\Column]
    private ?int $companyCapacity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(length: 199, nullable: true)]
    private ?string $fileName = null;

    #[ORM\Column(length: 199, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 199, nullable: true)]
    private ?string $videoPlaceholder = null;

    #[ORM\Column(length: 199, nullable: true)]
    private ?string $videoUrl = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $shortDescription = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 99, nullable: true)]
    private ?string $website = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $admissionCriteria = null;

    #[ORM\Column]
    private ?array $availableServices = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2, nullable: true)]
    private ?float $averageRating = null;

    /**
     * @var Collection<int, CategoryCare>
     */
    #[ORM\ManyToMany(targetEntity: CategoryCare::class, inversedBy: 'companies')]
    #[ORM\JoinTable(name: 'company_has_category_care')]
    private Collection $categoryCares;

    /**
     * @var Collection<int, CategoryService>
     */
    #[ORM\ManyToMany(targetEntity: CategoryService::class, inversedBy: 'companies')]
    #[ORM\JoinTable(name: 'company_has_category_service')]
    private Collection $categoryServices;

    /**
     * @var Collection<int, CompanyGallery>
     */
    #[ORM\OneToMany(targetEntity: CompanyGallery::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $companyGalleries;

    /**
     * @var Collection<int, Job>
     */
    #[ORM\OneToMany(targetEntity: Job::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $jobs;

    /**
     * @var Collection<int, TrainingCourse>
     */
    #[ORM\OneToMany(targetEntity: TrainingCourse::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $trainingCourses;

    /**
     * @var Collection<int, CompanyReview>
     */
    #[ORM\OneToMany(targetEntity: CompanyReview::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $companyReviews;


    /**
     * @var Collection<int, EventWinner>
     */
    #[ORM\OneToMany(targetEntity: EventWinner::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $eventWinners;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->categoryCares = new ArrayCollection();
        $this->categoryServices = new ArrayCollection();
        $this->companyGalleries = new ArrayCollection();
        $this->jobs = new ArrayCollection();
        $this->trainingCourses = new ArrayCollection();
        $this->companyReviews = new ArrayCollection();
        $this->eventWinners = new ArrayCollection();
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

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

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getLocationType(): ?string
    {
        return $this->locationType;
    }

    public function setLocationType(string $locationType): static
    {
        $this->locationType = $locationType;

        return $this;
    }

    public function getCompanyType(): ?string
    {
        return $this->companyType;
    }

    public function setCompanyType(string $companyType): static
    {
        $this->companyType = $companyType;

        return $this;
    }

    public function getCompanyCapacity(): ?int
    {
        return $this->companyCapacity;
    }

    public function setCompanyCapacity(int $companyCapacity): static
    {
        $this->companyCapacity = $companyCapacity;

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

    public function getFileName(): ?string
    {
        return $this->fileName ?: 'default.png';
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo ?: 'default.png';
    }

    public function setLogo(string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getVideoPlaceholder(): ?string
    {
        return $this->videoPlaceholder ?: 'default.png';
    }

    public function setVideoPlaceholder(string $videoPlaceholder): static
    {
        $this->videoPlaceholder = $videoPlaceholder;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getAdmissionCriteria(): ?string
    {
        return $this->admissionCriteria;
    }

    public function setAdmissionCriteria(?string $admissionCriteria): static
    {
        $this->admissionCriteria = $admissionCriteria;

        return $this;
    }

    public function getAvailableServices(): ?array
    {
        return $this->availableServices;
    }

    public function setAvailableServices(?array $availableServices): static
    {
        $this->availableServices = $availableServices;

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

    public function getAverageRating(): ?string
    {
        return $this->averageRating;
    }

    public function setAverageRating(?string $averageRating): static
    {
        $this->averageRating = $averageRating;

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

    public function setDeletedAt(\DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

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
     * @return Collection<int, CategoryCare>
     */
    public function getCategoryCares(): Collection
    {
        return $this->categoryCares;
    }

    public function addCategoryCare(CategoryCare $categoryCare): static
    {
        if (!$this->categoryCares->contains($categoryCare)) {
            $this->categoryCares->add($categoryCare);
        }

        return $this;
    }

    public function removeCategoryCare(CategoryCare $categoryCare): static
    {
        $this->categoryCares->removeElement($categoryCare);

        return $this;
    }

    /**
     * @return Collection<int, CategoryService>
     */
    public function getCategoryServices(): Collection
    {
        return $this->categoryServices;
    }

    public function addCategoryService(CategoryService $categoryService): static
    {
        if (!$this->categoryServices->contains($categoryService)) {
            $this->categoryServices->add($categoryService);
        }

        return $this;
    }

    public function removeCategoryService(CategoryService $categoryService): static
    {
        $this->categoryServices->removeElement($categoryService);

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

    /**
     * @return string[]
     */
    public static function getLocationTypes(): array
    {
        return [
            self::LOCATION_TYPE_CARE => self::LOCATION_TYPE_CARE,
            self::LOCATION_TYPE_PROVIDER => self::LOCATION_TYPE_PROVIDER
        ];
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
    public static function getServices(): array
    {
        return [
            "Camere dotate cu mobilier modern",
            "Evaluare zilnic a stării de sănătate",
            "Asistență medicală permanentă, îngrijire personalizată și administrarea medicației",
            "Medic de familie",
            "Analize medicale anuale gratuite (prin medicul de familie)",
            "Personal medical calificat",
            "Infirmieri calificați",
            "Analize medicale (contra cost)",
            "Gestionarea episoadelor acute de boală prin prescriere, aplicare și monitorizare tratament adecvat",
            "Asistență la deplasare în interiorul și în exteriorul centrului",
            "Terapie ocupațională, activități de recreere, activități sociale",
            "Spălătorie și etichetare haine",
            "Îngrijire personală (tuns, manichiură, pedichiură)"
        ];
    }

    /**
     * @return array
     */
    public static function getAdmissionCriteriaRange(): array
    {
        $values = [];

        foreach (range(35, 95, 10) as $item) {
            // Adăugăm valoarea în array
            $values[$item] = $item;
        }

        return $values;
    }

    /**
     * @return Collection<int, CompanyGallery>
     */
    public function getCompanyGalleries(): Collection
    {
        return $this->companyGalleries;
    }

    public function addCompanyGallery(CompanyGallery $companyGallery): static
    {
        if (!$this->companyGalleries->contains($companyGallery)) {
            $this->companyGalleries->add($companyGallery);
            $companyGallery->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyGallery(CompanyGallery $companyGallery): static
    {
        if ($this->companyGalleries->removeElement($companyGallery)) {
            // set the owning side to null (unless already changed)
            if ($companyGallery->getCompany() === $this) {
                $companyGallery->setCompany(null);
            }
        }

        return $this;
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
            $job->setCompany($this);
        }

        return $this;
    }

    public function removeJob(Job $job): static
    {
        if ($this->jobs->removeElement($job)) {
            // set the owning side to null (unless already changed)
            if ($job->getCompany() === $this) {
                $job->setCompany(null);
            }
        }

        return $this;
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
            $trainingCourse->setCompany($this);
        }

        return $this;
    }

    public function removeTrainingCourse(TrainingCourse $trainingCourse): static
    {
        if ($this->trainingCourses->removeElement($trainingCourse)) {
            // set the owning side to null (unless already changed)
            if ($trainingCourse->getCompany() === $this) {
                $trainingCourse->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CompanyReview>
     */
    public function getCompanyReviews(): Collection
    {
        return $this->companyReviews;
    }

    /**
     * @return Collection
     */
    public function getApprovedReviews(): Collection
    {
        return $this->companyReviews->filter(function (CompanyReview $review) {
            return $review->getStatus() === CompanyReview::STATUS_APPROVED && $review->getDeletedAt() === null;
        });
    }

    /**
     * @return CompanyReview|false|mixed|null
     */
    public function getFirstReview(): mixed
    {
        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('status', CompanyReview::STATUS_APPROVED))
            ->orderBy(['createdAt' => 'DESC'])
            ->setMaxResults(1);

        $reviews = $this->companyReviews->matching($criteria);

        return $reviews->isEmpty() ? null : $reviews->first()->getReview();
    }


    public function addCompanyReview(CompanyReview $companyReview): static
    {
        if (!$this->companyReviews->contains($companyReview)) {
            $this->companyReviews->add($companyReview);
            $companyReview->setCompany($this);
        }

        return $this;
    }

    public function removeCompanyReview(CompanyReview $companyReview): static
    {
        if ($this->companyReviews->removeElement($companyReview)) {
            // set the owning side to null (unless already changed)
            if ($companyReview->getCompany() === $this) {
                $companyReview->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return void
     */
    public function updateCompanyReviewsRating(): void
    {
        $total = 0;
        $reviews = $this->getApprovedReviews();
        $count = $reviews->count();

        /** @var CompanyReview $review */
        foreach ($reviews as $review) {
            $total += $review->getTotalValuesStar();
        }

        $this->averageRating = $count === 0 ? null : number_format($total / $count, 2);
    }

    /**
     * @return Collection<int, EventWinner>
     */
    public function getEventWinners(): Collection
    {
        return $this->eventWinners;
    }

    public function addEventWinner(EventWinner $eventWinner): static
    {
        if (!$this->eventWinners->contains($eventWinner)) {
            $this->eventWinners->add($eventWinner);
            $eventWinner->setCompany($this);
        }

        return $this;
    }

    public function removeEventWinner(EventWinner $eventWinner): static
    {
        if ($this->eventWinners->removeElement($eventWinner)) {
            // set the owning side to null (unless already changed)
            if ($eventWinner->getCompany() === $this) {
                $eventWinner->setCompany(null);
            }
        }

        return $this;
    }
}
