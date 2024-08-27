<?php

namespace App\Entity;

use App\Repository\EducationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: EducationRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'An education with this slug already exists.', errorPath: 'title')]
class Education
{

    const TYPE_COURSE = 'course';
    const TYPE_WORKSHOP = 'workshop';
    const TYPE_CONVENTION = 'convention';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\ManyToOne(inversedBy: 'educations')]
    private ?County $county = null;

    #[ORM\ManyToOne(inversedBy: 'educations')]
    private ?City $city = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 200)]
    private ?string $location = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    private ?string $price = null;

    #[ORM\Column]
    private ?int $vat = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(options: ['default' => 1])]
    private ?bool $allowRegistrations = true;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $omcCode = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\OneToMany(targetEntity: EducationSchedule::class, mappedBy: 'education', cascade: ["persist"], orphanRemoval: true)]
    private Collection $schedules;

    /**
     * @var Collection<int, EducationTranslation>
     */
    #[ORM\OneToMany(targetEntity: EducationTranslation::class, mappedBy: 'education', orphanRemoval: true)]
    private Collection $educationTranslations;

    #[ORM\ManyToOne(inversedBy: 'education')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Certification $certification = null;

    #[ORM\Column(nullable: true)]
    private ?int $discount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $discountStartDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $discountEndDate = null;

    /**
     * @var Collection<int, TeamMember>
     */
    #[ORM\ManyToMany(targetEntity: TeamMember::class)]
    private Collection $teamMembers;

    /**
     * @var Collection<int, EducationRegistration>
     */
    #[ORM\OneToMany(targetEntity: EducationRegistration::class, mappedBy: 'education')]
    private Collection $educationRegistrations;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $contractOccupation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contractDuration = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $invoiceServiceName = null;

    private $defaultLocale = 'ro';

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->educationTranslations = new ArrayCollection();
        $this->teamMembers = new ArrayCollection();
        $this->schedules = new ArrayCollection();
        $this->educationRegistrations = new ArrayCollection();
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

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

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

    public function getVat(): ?int
    {
        return $this->vat;
    }

    public function setVat(int $vat): static
    {
        $this->vat = $vat;

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

    public function isAllowRegistrations(): ?bool
    {
        return $this->allowRegistrations;
    }

    public function setAllowRegistrations(bool $allowRegistrations): static
    {
        $this->allowRegistrations = $allowRegistrations;

        return $this;
    }

    public function isPaymentInInstallments(): ?bool
    {
        return $this->paymentInInstallments;
    }

    public function setPaymentInInstallments(bool $paymentInInstallments): static
    {
        $this->paymentInInstallments = $paymentInInstallments;

        return $this;
    }

    public function getOmcCode(): ?string
    {
        return $this->omcCode;
    }

    public function setOmcCode(?string $omcCode): static
    {
        $this->omcCode = $omcCode;

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
     * @return Collection<int, EducationTranslation>
     */
    public function getEducationTranslations(): Collection
    {
        return $this->educationTranslations;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getCertification(): ?Certification
    {
        return $this->certification;
    }

    public function setCertification(?Certification $certification): static
    {
        $this->certification = $certification;

        return $this;
    }

    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    public function setDiscount(?int $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getDiscountStartDate(): ?\DateTimeInterface
    {
        return $this->discountStartDate;
    }

    public function setDiscountStartDate(?\DateTimeInterface $discountStartDate): static
    {
        $this->discountStartDate = $discountStartDate;

        return $this;
    }

    public function getDiscountEndDate(): ?\DateTimeInterface
    {
        return $this->discountEndDate;
    }

    public function setDiscountEndDate(?\DateTimeInterface $discountEndDate): static
    {
        $this->discountEndDate = $discountEndDate;

        return $this;
    }

    public function getBasePrice()
    {
        $price = $this->price;

        if (null !== $this->discount &&
            $this->discountStartDate instanceof \DateTime &&
            $this->discountEndDate instanceof \DateTime) {
            $now = new \DateTime();

            if ($now >= $this->discountStartDate && $now <= $this->discountEndDate) {
                $price = round($price - ($price * ($this->discount / 100)), 2);
            }
        }

        return $price;
    }

    public function getPriceWithVAT()
    {
        $price = $this->getBasePrice();

        return round($price * (1 + $this->vat / 100), 2);
    }

    public function getVATAddedValue()
    {
        $basePrice = $this->getBasePrice();
        $priceWithVAT = $this->getPriceWithVAT();

        return round($priceWithVAT - $basePrice, 2);
    }

    public function getTranslation($locale)
    {
        $translations = $this->getEducationTranslations()->filter(function ($educationTranslation) use ($locale) {
            return $educationTranslation->getLanguage()->getLocale() === $locale;
        });

        if ($translations->isEmpty()) {
            return $this->getEducationTranslations()->filter(function ($educationTranslation) {
                return $educationTranslation->getLanguage()->getLocale() === $this->defaultLocale;
            })->first();
        }

        return $translations->first();
    }

    /**
     * @return Collection<int, TeamMember>
     */
    public function getTeamMembers(): Collection
    {
        return $this->teamMembers;
    }

    public function addTeamMember(TeamMember $teamMember): static
    {
        if (!$this->teamMembers->contains($teamMember)) {
            $this->teamMembers->add($teamMember);
        }

        return $this;
    }

    public function removeTeamMember(TeamMember $teamMember): static
    {
        $this->teamMembers->removeElement($teamMember);

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
     * @return Collection|EducationSchedule[]
     */
    public function getSchedules(): Collection
    {
        return $this->schedules;
    }

    public function addSchedule(EducationSchedule $schedule): self
    {
        if (!$this->schedules->contains($schedule)) {
            $this->schedules[] = $schedule;
            $schedule->setEducation($this);
        }

        return $this;
    }

    public function removeSchedule(EducationSchedule $schedule): self
    {
        if ($this->schedules->removeElement($schedule)) {
            // Set the owning side to null (unless already changed)
            if ($schedule->getEducation() === $this) {
                $schedule->setEducation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, EducationRegistration>
     */
    public function getEducationRegistrations(): Collection
    {
        return $this->educationRegistrations;
    }

    public function addEducationRegistration(EducationRegistration $educationRegistration): static
    {
        if (!$this->educationRegistrations->contains($educationRegistration)) {
            $this->educationRegistrations->add($educationRegistration);
            $educationRegistration->setEducation($this);
        }

        return $this;
    }

    public function removeEducationRegistration(EducationRegistration $educationRegistration): static
    {
        if ($this->educationRegistrations->removeElement($educationRegistration)) {
            // set the owning side to null (unless already changed)
            if ($educationRegistration->getEducation() === $this) {
                $educationRegistration->setEducation(null);
            }
        }

        return $this;
    }

    public function getContractOccupation(): ?string
    {
        return $this->contractOccupation;
    }

    public function setContractOccupation(?string $contractOccupation): static
    {
        $this->contractOccupation = $contractOccupation;

        return $this;
    }

    public function getContractDuration(): ?string
    {
        return $this->contractDuration;
    }

    public function setContractDuration(?string $contractDuration): static
    {
        $this->contractDuration = $contractDuration;

        return $this;
    }

    public function getInvoiceServiceName(): ?string
    {
        return $this->invoiceServiceName;
    }

    public function setInvoiceServiceName(?string $invoiceServiceName): static
    {
        $this->invoiceServiceName = $invoiceServiceName;

        return $this;
    }

    public function getFormattedDate($locale = 'ro')
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        if (!$startDate instanceof \DateTime || !$endDate instanceof \DateTime) {
            throw new \InvalidArgumentException('startDate and endDate must be instances of DateTime');
        }

        $months = [
            'January' => 'Ianuarie',
            'February' => 'Februarie',
            'March' => 'Martie',
            'April' => 'Aprilie',
            'May' => 'Mai',
            'June' => 'Iunie',
            'July' => 'Iulie',
            'August' => 'August',
            'September' => 'Septembrie',
            'October' => 'Octombrie',
            'November' => 'Noiembrie',
            'December' => 'Decembrie',
        ];

        $startMonth = $locale === 'ro' ? $months[$startDate->format('F')] : $startDate->format('F');
        $endMonth = $locale === 'ro' ? $months[$endDate->format('F')] : $endDate->format('F');

        if ($startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
            return $formattedDate = $startDate->format('d') . ' ' . $startMonth . ' ' . $startDate->format('Y');
        }

        if ($startDate->format('Y') === $endDate->format('Y')) {

            if ($startDate->format('F') === $endDate->format('F')) {
                return $formattedDate = $startDate->format('d') . ' - ' . $endDate->format('d') . ' ' . $endMonth . ' ' . $endDate->format('Y');
            }

            return $formattedDate = $startDate->format('d') . ' ' . $startMonth . ' - ' . $endDate->format('d') . ' ' . $endMonth . ' ' . $endDate->format('Y');
        }

        return $formattedDate = $startDate->format('d') . ' ' . $startMonth . ' ' . $startDate->format('Y') . ' - ' . $endDate->format('d') . ' ' . $endMonth . ' ' . $endDate->format('Y');
    }
}
