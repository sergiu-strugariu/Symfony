<?php

namespace App\Entity;

use App\Repository\EducationRegistrationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EducationRegistrationRepository::class)]
class EducationRegistration
{
    const PAYMENT_TYPE_WIRE = 'wire';
    const PAYMENT_TYPE_CARD = 'card';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_SUCCESS = 'success';
    const PAYMENT_STATUS_FAILED = 'failed';

    const COMPANY_FIELDS = ['companyName', 'companyAddress', 'cui', 'registrationNumber', 'bankName', 'bankAccount'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\ManyToOne(inversedBy: 'educationRegistrations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'educationRegistrations')]
    private ?Education $education = null;
    
    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cui = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $registrationNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bankName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bankAccount = null;
    
    #[ORM\Column(options: ['default' => 0])]
    private ?bool $invoicingPerLegalEntity = false;

    #[ORM\Column(length: 255)]
    private ?string $paymentMethod = null;
    
    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    private ?string $paymentAmount = null;

    #[ORM\Column]
    private ?int $paymentVat = null;

    #[ORM\Column(length: 255)]
    private ?string $paymentStatus = null;
    
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $paymentMessage = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $payuPaymentReference = null;

    #[ORM\Column]
    private ?bool $accordGDPR = null;

    #[ORM\Column]
    private ?bool $contract = null;

    #[ORM\Column]
    private ?bool $accordMedia = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?array $payuIpnRequest = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $invoiceNumber = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $invoiceSeriesName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $proformaInvoiceNumber = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $proformaInvoiceSeriesName = null;

    #[ORM\Column(nullable: true)]
    private ?int $contractNumber = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $certificateFileName = null;
    
    public function __construct() {
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

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

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

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getCompanyAddress(): ?string
    {
        return $this->companyAddress;
    }

    public function setCompanyAddress(string $companyAddress): static
    {
        $this->companyAddress = $companyAddress;

        return $this;
    }

    public function getCui(): ?string
    {
        return $this->cui;
    }

    public function setCui(string $cui): static
    {
        $this->cui = $cui;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(string $registrationNumber): static
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(string $bankName): static
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getBankAccount(): ?string
    {
        return $this->bankAccount;
    }

    public function setBankAccount(string $bankAccount): static
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getPaymentStatus(): ?string
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(string $paymentStatus): static
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function isAccordGDPR(): ?bool
    {
        return $this->accordGDPR;
    }

    public function setAccordGDPR(bool $accordGDPR): static
    {
        $this->accordGDPR = $accordGDPR;

        return $this;
    }

    public function isContract(): ?bool
    {
        return $this->contract;
    }

    public function setContract(bool $contract): static
    {
        $this->contract = $contract;

        return $this;
    }

    public function isAccordMedia(): ?bool
    {
        return $this->accordMedia;
    }

    public function setAccordMedia(bool $accordMedia): static
    {
        $this->accordMedia = $accordMedia;

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

    public function getEducation(): ?Education
    {
        return $this->education;
    }

    public function setEducation(?Education $education): static
    {
        $this->education = $education;

        return $this;
    }

    public function getPaymentMessage(): ?string
    {
        return $this->paymentMessage;
    }

    public function setPaymentMessage(?string $paymentMessage): static
    {
        $this->paymentMessage = $paymentMessage;

        return $this;
    }

    public function getPayuPaymentReference(): ?string
    {
        return $this->payuPaymentReference;
    }

    public function setPayuPaymentReference(?string $payuPaymentReference): static
    {
        $this->payuPaymentReference = $payuPaymentReference;

        return $this;
    }

    public function isInvoicingPerLegalEntity(): ?bool
    {
        return $this->invoicingPerLegalEntity;
    }

    public function setInvoicingPerLegalEntity(bool $invoicingPerLegalEntity): static
    {
        $this->invoicingPerLegalEntity = $invoicingPerLegalEntity;

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

    public function getPaymentAmount(): ?string
    {
        return $this->paymentAmount;
    }

    public function setPaymentAmount(string $paymentAmount): static
    {
        $this->paymentAmount = $paymentAmount;

        return $this;
    }

    public function getPaymentVat(): ?int
    {
        return $this->paymentVat;
    }

    public function setPaymentVat(int $paymentVat): static
    {
        $this->paymentVat = $paymentVat;

        return $this;
    }

    public function getPayuIpnRequest(): ?array
    {
        return $this->payuIpnRequest;
    }

    public function setPayuIpnRequest(?array $payuIpnRequest): static
    {
        $this->payuIpnRequest = $payuIpnRequest;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->getFirstName() . " " . $this->getLastName();
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): static
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getInvoiceSeriesName(): ?string
    {
        return $this->invoiceSeriesName;
    }

    public function setInvoiceSeriesName(?string $invoiceSeriesName): static
    {
        $this->invoiceSeriesName = $invoiceSeriesName;

        return $this;
    }

    public function getProformaInvoiceNumber(): ?string
    {
        return $this->proformaInvoiceNumber;
    }

    public function setProformaInvoiceNumber(?string $proformaInvoiceNumber): static
    {
        $this->proformaInvoiceNumber = $proformaInvoiceNumber;

        return $this;
    }

    public function getProformaInvoiceSeriesName(): ?string
    {
        return $this->proformaInvoiceSeriesName;
    }

    public function setProformaInvoiceSeriesName(?string $proformaInvoiceSeriesName): static
    {
        $this->proformaInvoiceSeriesName = $proformaInvoiceSeriesName;

        return $this;
    }
    
    public function getPaymentWithVAT() {
        $price = $this->paymentAmount;
        
        return round($price * (1 + $this->paymentVat / 100), 2);
    }

    public function getContractNumber(): ?int
    {
        return $this->contractNumber;
    }

    public function setContractNumber(?int $contractNumber): static
    {
        $this->contractNumber = $contractNumber;

        return $this;
    }

    public function getCertificateFileName(): ?string
    {
        return $this->certificateFileName;
    }

    public function setCertificateFileName(?string $certificateFileName): static
    {
        $this->certificateFileName = $certificateFileName;

        return $this;
    }
}
