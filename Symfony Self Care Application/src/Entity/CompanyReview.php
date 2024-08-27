<?php

namespace App\Entity;

use App\Repository\CompanyReviewRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompanyReviewRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_review_company', columns: ['company_id', 'email'])]
class CompanyReview
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';

    CONST CONNECTIONS = ['Connection 1', 'Connection 2', 'Connection 3'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'companyReviews')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'companyReviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\Column(length: 199)]
    private ?string $name = null;

    #[ORM\Column(length: 199)]
    private ?string $surname = null;

    #[ORM\Column(length: 199)]
    private ?string $email = null;

    #[ORM\Column(length: 199)]
    private ?string $phone = null;

    #[ORM\Column(length: 199, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(length: 199)]
    private ?string $connection = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $review = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $generalStar = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $facilityStar = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $maintenanceStar = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $cleanStar = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $dignityStar = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $beverageStar = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $personalStar = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $activityStar = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $securityStar = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $managementStar = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $roomStar = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $priceQualityStar = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    private ?float $totalValuesStar = null;

    #[ORM\Column]
    private ?bool $nameAgree = null;

    #[ORM\Column]
    private ?bool $ratingAgree = null;

    #[ORM\Column(length: 10)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\Column]
    private ?bool $emailSent = null;

    public function __construct()
    {
        $this->status = self::STATUS_PENDING;
        $this->emailSent = false;
        $this->createdAt = new DateTime();
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

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getConnection(): ?string
    {
        return $this->connection;
    }

    public function setConnection(string $connection): static
    {
        $this->connection = $connection;

        return $this;
    }

    public function getReview(): ?string
    {
        return $this->review;
    }

    public function setReview(string $review): static
    {
        $this->review = $review;

        return $this;
    }

    public function getGeneralStar(): ?int
    {
        return $this->generalStar;
    }

    public function setGeneralStar(int $generalStar): static
    {
        $this->generalStar = $generalStar;

        return $this;
    }

    public function getFacilityStar(): ?int
    {
        return $this->facilityStar;
    }

    public function setFacilityStar(int $facilityStar): static
    {
        $this->facilityStar = $facilityStar;

        return $this;
    }

    public function getMaintenanceStar(): ?int
    {
        return $this->maintenanceStar;
    }

    public function setMaintenanceStar(int $maintenanceStar): static
    {
        $this->maintenanceStar = $maintenanceStar;

        return $this;
    }

    public function getCleanStar(): ?int
    {
        return $this->cleanStar;
    }

    public function setCleanStar(int $cleanStar): static
    {
        $this->cleanStar = $cleanStar;

        return $this;
    }

    public function getDignityStar(): ?int
    {
        return $this->dignityStar;
    }

    public function setDignityStar(int $dignityStar): static
    {
        $this->dignityStar = $dignityStar;

        return $this;
    }

    public function getBeverageStar(): ?int
    {
        return $this->beverageStar;
    }

    public function setBeverageStar(int $beverageStar): static
    {
        $this->beverageStar = $beverageStar;

        return $this;
    }

    public function getPersonalStar(): ?int
    {
        return $this->personalStar;
    }

    public function setPersonalStar(int $personalStar): static
    {
        $this->personalStar = $personalStar;

        return $this;
    }

    public function getActivityStar(): ?int
    {
        return $this->activityStar;
    }

    public function setActivityStar(int $activityStar): static
    {
        $this->activityStar = $activityStar;

        return $this;
    }

    public function getSecurityStar(): ?int
    {
        return $this->securityStar;
    }

    public function setSecurityStar(int $securityStar): static
    {
        $this->securityStar = $securityStar;

        return $this;
    }

    public function getManagementStar(): ?int
    {
        return $this->managementStar;
    }

    public function setManagementStar(int $managementStar): static
    {
        $this->managementStar = $managementStar;

        return $this;
    }

    public function getRoomStar(): ?int
    {
        return $this->roomStar;
    }

    public function setRoomStar(int $roomStar): static
    {
        $this->roomStar = $roomStar;

        return $this;
    }

    public function getPriceQualityStar(): ?int
    {
        return $this->priceQualityStar;
    }

    public function setPriceQualityStar(int $priceQualityStar): static
    {
        $this->priceQualityStar = $priceQualityStar;

        return $this;
    }

    public function getTotalValuesStar(): ?string
    {
        return $this->totalValuesStar;
    }

    public function setTotalValuesStar(string $totalValuesStar): static
    {
        $this->totalValuesStar = $totalValuesStar;

        return $this;
    }

    public function isNameAgree(): ?bool
    {
        return $this->nameAgree;
    }

    public function setNameAgree(bool $nameAgree): static
    {
        $this->nameAgree = $nameAgree;

        return $this;
    }

    public function isRatingAgree(): ?bool
    {
        return $this->ratingAgree;
    }

    public function setRatingAgree(bool $ratingAgree): static
    {
        $this->ratingAgree = $ratingAgree;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

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

    public function isEmailSent(): ?bool
    {
        return $this->emailSent;
    }

    public function setEmailSent(bool $emailSent): static
    {
        $this->emailSent = $emailSent;

        return $this;
    }

    /**
     * @return int|float|null
     */
    public function calculateTotalValuesStar(): int|null|float
    {
        return
            ($this->generalStar +
                $this->facilityStar +
                $this->maintenanceStar +
                $this->cleanStar +
                $this->dignityStar +
                $this->beverageStar +
                $this->personalStar +
                $this->activityStar +
                $this->securityStar +
                $this->managementStar +
                $this->roomStar +
                $this->priceQualityStar) / 12;
    }
}
