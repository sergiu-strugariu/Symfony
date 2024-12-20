<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @ORM\Entity(repositoryClass=PaymentRepository::class)
 */
class Payment
{
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_CONFIRMED = 'confirmed';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_CANCELED = 'canceled';
    public const PAYMENT_STATUS_FAILED = 'failed';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=40, unique=true)
     */
    private ?string $uuid = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user;

    /**
     * @ORM\ManyToOne(targetEntity=UserBillingData::class, inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?UserBillingData $userBillingData;

    /**
     * @ORM\ManyToOne(targetEntity=MembershipPackage::class, inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?MembershipPackage $membershipPackage;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private ?string $status = null;

    /**
     * @ORM\Column(type="string", length=40)
     */
    private ?string $plan = null;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2)
     */
    private ?string $price = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $paymentMessage = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $membershipExpiresAt = null;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private ?bool $processed = false;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private ?string $invoiceSeriesName = null;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private ?string $invoiceNumber = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
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

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;
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

    public function getPlan(): ?string
    {
        return $this->plan;
    }

    public function setPlan(string $plan): self
    {
        $this->plan = $plan;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getPaymentMessage(): ?string
    {
        return $this->paymentMessage;
    }

    public function setPaymentMessage(?string $paymentMessage): self
    {
        $this->paymentMessage = $paymentMessage;
        return $this;
    }

    public function getMembershipExpiresAt(): ?\DateTimeInterface
    {
        return $this->membershipExpiresAt;
    }

    public function setMembershipExpiresAt(?\DateTimeInterface $membershipExpiresAt): self
    {
        $this->membershipExpiresAt = $membershipExpiresAt;
        return $this;
    }

    public function isProcessed(): ?bool
    {
        return $this->processed;
    }

    public function setProcessed(bool $processed): self
    {
        $this->processed = $processed;
        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;
        return $this;
    }

    public function getInvoiceSeriesName(): ?string
    {
        return $this->invoiceSeriesName;
    }

    public function setInvoiceSeriesName(?string $invoiceSeriesName): self
    {
        $this->invoiceSeriesName = $invoiceSeriesName;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUserBillingData(): ?UserBillingData
    {
        return $this->userBillingData;
    }

    public function setUserBillingData(?UserBillingData $userBillingData): self
    {
        $this->userBillingData = $userBillingData;

        return $this;
    }

    public function getMembershipPackage(): ?MembershipPackage
    {
        return $this->membershipPackage;
    }

    public function setMembershipPackage(?MembershipPackage $membershipPackage): self
    {
        $this->membershipPackage = $membershipPackage;

        return $this;
    }
}