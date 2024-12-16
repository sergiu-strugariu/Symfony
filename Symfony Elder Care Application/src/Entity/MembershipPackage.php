<?php

namespace App\Entity;

use App\Repository\MembershipPackageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=MembershipPackageRepository::class)
 * @ORM\Table(name="user_membership_package")
 * @UniqueEntity(fields={"slug"}, errorPath="name", message="This slug is already used. Please modify the name.")
 */
class MembershipPackage
{
    const MODULE_ADMINISTRATIVE = 'administrativeModule';
    const MODULE_MEDICAL = 'medicalModule';
    const MODULE_PHYSIOTHERAPY = 'physiotherapyModule';
    const MODULE_INFIRMARY = 'infirmaryModule';
    const MODULE_RECEPTION = 'receptionModule';
    const MODULE_KITCHEN = 'kitchenModule';

    const MODULES = [
        self::MODULE_ADMINISTRATIVE => [
            'title' => 'Modul administrativ',
            'generalReport' => [
                'title' => 'Raport general (dashboard)',
                'icon' => 'item-1.svg',
                'enabled' => false
            ],
            'unitManagement' => [
                'title' => 'Administrare unități',
                'icon' => 'item-2.svg',
                'enabled' => false
            ],
            'locationManagement' => [
                'title' => 'Administrare locații',
                'icon' => 'item-3.svg',
                'enabled' => false
            ],
            'roomManagement' => [
                'title' => 'Administrare camere',
                'icon' => 'item-4.svg',
                'enabled' => false
            ],
            'userManagement' => [
                'title' => 'Administrare utilizatori',
                'icon' => 'item-5.svg',
                'enabled' => false
            ],
            'profileManagement' => [
                'title' => 'Administrare profil',
                'icon' => 'item-6.svg',
                'enabled' => false
            ],
            'prospectList' => [
                'title' => 'Lista prospecți',
                'icon' => 'item-7.svg',
                'enabled' => false
            ],
            'prospectManagement' => [
                'title' => 'Management prospecți',
                'icon' => 'item-8.svg',
                'enabled' => false
            ],
            'viewingScheduling' => [
                'title' => 'Programare vizionare & listă vizionări',
                'icon' => 'item-9.svg',
                'enabled' => false
            ],
            'financialOffer' => [
                'title' => 'Trimitere ofertă financiară & listare oferte trimise',
                'icon' => 'item-10.svg',
                'enabled' => false
            ],
            'leadSourceReport' => [
                'title' => 'Raport sursa lead-uri',
                'icon' => 'item-11.svg',
                'enabled' => false
            ],
            'annualProspectsReport' => [
                'title' => 'Raport anual prospecți',
                'icon' => 'item-12.svg',
                'enabled' => false
            ],
            'documentsAtAdmission' => [
                'title' => 'Întocmire documente la internare/externare',
                'icon' => 'item-13.svg',
                'enabled' => false
            ],
            'patientFileReport' => [
                'title' => 'Raport dosare digitale per pacient',
                'icon' => 'item-14.svg',
                'enabled' => false
            ],
            'crmUsageReport' => [
                'title' => 'Raport activitate utilizare CRM',
                'icon' => 'item-15.svg',
                'enabled' => false
            ],
            'familyReport' => [
                'title' => 'Raport apartinatori',
                'icon' => 'item-16.svg',
                'enabled' => false
            ]
        ],
        self::MODULE_MEDICAL => [
            'title' => 'Modul medical',
            'patientListing' => [
                'title' => 'Listare pacienți',
                'icon' => 'item-17.svg',
                'enabled' => false
            ],
            'patientAdmission' => [
                'title' => 'Internare pacienți',
                'icon' => 'item-18.svg',
                'enabled' => false
            ],
            'patientDischarge' => [
                'title' => 'Externare pacienți',
                'icon' => 'item-19.svg',
                'enabled' => false
            ],
            'patientDetailsReports' => [
                'title' => 'Detalii pacient & rapoarte generale',
                'icon' => 'item-20.svg',
                'enabled' => false
            ],
            'patientData' => [
                'title' => 'Date pacient',
                'icon' => 'item-21.svg',
                'enabled' => false
            ],
            'patientGuardians' => [
                'title' => 'Aparținători pacient',
                'icon' => 'item-22.svg',
                'enabled' => false
            ],
            'digitalFile' => [
                'title' => 'Dosar digital',
                'icon' => 'item-23.svg',
                'enabled' => false
            ],
            'treatmentSchemes' => [
                'title' => 'Scheme tratament & diagnostice',
                'icon' => 'item-24.svg',
                'enabled' => false
            ],
            'diagnosticAndTreatmentReport' => [
                'title' => 'Raport diagnostice și tratamente per pacient',
                'icon' => 'item-25.svg',
                'enabled' => false
            ],
            'borderoListing' => [
                'title' => 'Listă borderouri',
                'icon' => 'item-26.svg',
                'enabled' => false
            ],
            'vitalSignsMonitoring' => [
                'title' => 'Monitorizare funcții vitale pacienți per tură & etaj',
                'icon' => 'item-27.svg',
                'enabled' => false
            ]
        ],
        self::MODULE_PHYSIOTHERAPY => [
            'title' => 'Modul kinetoterapie',
            'borderouList' => [
                'title' => 'Listă borderouri',
                'icon' => 'item-28.svg',
                'enabled' => false
            ],
            'procedureMonitoring' => [
                'title' => 'Monitorizare proceduri kinetoterapie pacienți per tură & etaj',
                'icon' => 'item-29.svg',
                'enabled' => false
            ]
        ],
        self::MODULE_INFIRMARY => [
            'title' => 'Modul infirmerie',
            'borderouList' => [
                'title' => 'Listă borderouri',
                'icon' => 'item-30.svg',
                'enabled' => false
            ],
            'patientMonitoring' => [
                'title' => 'Monitorizare pacienti per tura & etaj',
                'icon' => 'item-31.svg',
                'enabled' => false
            ],
        ],
        self::MODULE_RECEPTION => [
            'title' => 'Modul recepție',
            'visitCalendar' => [
                'title' => 'Calendar vizite',
                'icon' => 'item-32.svg',
                'enabled' => false
            ],
            'viewAndAddVisits' => [
                'title' => 'Vizualizare & adaugăre vizite',
                'icon' => 'item-33.svg',
                'enabled' => false
            ],
            'visitList' => [
                'title' => 'Listă vizite',
                'icon' => 'item-34.svg',
                'enabled' => false
            ],
        ],
        self::MODULE_KITCHEN => [
            'title' => 'Modul bucătărie',
            'borderoListing' => [
                'title' => 'Listă borderouri',
                'icon' => 'item-35.svg',
                'enabled' => false
            ],
            'preferredFoodManagement' => [
                'title' => 'Administrare mancare preferențială pacienți per etaj',
                'icon' => 'item-36.svg',
                'enabled' => false
            ],
            'dailyMenuManagement' => [
                'title' => 'Administrare meniuri mancare per zi',
                'icon' => 'item-37.svg',
                'enabled' => false
            ],
            'weeklyMenuReport' => [
                'title' => 'Raport meniuri săptămânale',
                'icon' => 'item-38.svg',
                'enabled' => false
            ],
            'preferredMenuReport' => [
                'title' => 'Raport listă bucătărie meniuri preferințiale, per zi',
                'icon' => 'item-39.svg',
                'enabled' => false
            ],
        ]
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     */
    private ?string $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $subName = null;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $content = null;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private ?string $slug = null;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private ?int $price = 0;

    /**
     * @ORM\Column(type="smallint")
     */
    private ?int $discount = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $fileName = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $isFree = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $isPopular = false;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private ?string $status = null;

    /**
     * @ORM\Column(type="json")
     */
    private array $administrativeModule = [];

    /**
     * @ORM\Column(type="json")
     */
    private array $medicalModule = [];

    /**
     * @ORM\Column(type="json")
     */
    private array $physiotherapyModule = [];

    /**
     * @ORM\Column(type="json")
     */
    private array $infirmaryModule = [];

    /**
     * @ORM\Column(type="json")
     */
    private array $receptionModule = [];

    /**
     * @ORM\Column(type="json")
     */
    private array $kitchenModule = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $deletedAt = null;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPrice(): ?string
    {
        return round($this->price, 2);
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    public function setDiscount(int $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function isIsPopular(): ?bool
    {
        return $this->isPopular;
    }

    public function setIsPopular(bool $isPopular): self
    {
        $this->isPopular = $isPopular;

        return $this;
    }

    public function isIsFree(): ?bool
    {
        return $this->isFree;
    }

    public function setIsFree(bool $isFree): self
    {
        $this->isFree = $isFree;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSubName(): ?string
    {
        return $this->subName;
    }

    public function setSubName(string $subName): self
    {
        $this->subName = $subName;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getAdministrativeModule(): ?array
    {
        return $this->administrativeModule;
    }

    public function setAdministrativeModule(array $administrativeModule): self
    {
        $this->administrativeModule = $administrativeModule;

        return $this;
    }

    public function getMedicalModule(): ?array
    {
        return $this->medicalModule;
    }

    public function setMedicalModule(array $medicalModule): self
    {
        $this->medicalModule = $medicalModule;

        return $this;
    }

    public function getPhysiotherapyModule(): ?array
    {
        return $this->physiotherapyModule;
    }

    public function setPhysiotherapyModule(array $physiotherapyModule): self
    {
        $this->physiotherapyModule = $physiotherapyModule;

        return $this;
    }

    public function getInfirmaryModule(): ?array
    {
        return $this->infirmaryModule;
    }

    public function setInfirmaryModule(array $infirmaryModule): self
    {
        $this->infirmaryModule = $infirmaryModule;

        return $this;
    }

    public function getReceptionModule(): ?array
    {
        return $this->receptionModule;
    }

    public function setReceptionModule(array $receptionModule): self
    {
        $this->receptionModule = $receptionModule;

        return $this;
    }

    public function getKitchenModule(): ?array
    {
        return $this->kitchenModule;
    }

    public function setKitchenModule(array $kitchenModule): self
    {
        $this->kitchenModule = $kitchenModule;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * @return float
     */
    public function getYearlyPricePerMonth(): float
    {
        if ($this->discount > 0) {
            return round($this->price - ($this->price * ($this->discount / 100)), 2);
        }

        return round($this->price, 2);
    }
}
