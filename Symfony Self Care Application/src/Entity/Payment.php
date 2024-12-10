<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_CONFIRMED = 'confirmed';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_CANCELED = 'canceled';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserBillingData $userBillingData = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MembershipPackage $membershipPackage = null;

    #[ORM\Column(length: 40)]
    private ?string $status = null;

    #[ORM\Column(length: 40)]
    private ?string $plan = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $paymentMessage = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $membershipExpiresAt = null;

    #[ORM\Column(options: ['default' => 0])]
    private ?bool $processed = false;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $invoiceSeriesName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $invoiceNumber = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->uuid = Uuid::v4();
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

    public function getUserBillingData(): ?UserBillingData
    {
        return $this->userBillingData;
    }

    public function setUserBillingData(?UserBillingData $userBillingData): static
    {
        $this->userBillingData = $userBillingData;

        return $this;
    }

    public function getMembershipPackage(): ?MembershipPackage
    {
        return $this->membershipPackage;
    }

    public function setMembershipPackage(?MembershipPackage $membershipPackage): static
    {
        $this->membershipPackage = $membershipPackage;

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

    public function getPlan(): ?string
    {
        return $this->plan;
    }

    public function setPlan(string $plan): static
    {
        $this->plan = $plan;

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

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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

    public function getMembershipExpiresAt(): ?\DateTimeInterface
    {
        return $this->membershipExpiresAt;
    }

    public function setMembershipExpiresAt(?\DateTimeInterface $membershipExpiresAt): static
    {
        $this->membershipExpiresAt = $membershipExpiresAt;

        return $this;
    }

    public function isProcessed(): ?bool
    {
        return $this->processed;
    }

    public function setProcessed(bool $processed): static
    {
        $this->processed = $processed;

        return $this;
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
}
