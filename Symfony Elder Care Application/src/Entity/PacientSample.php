<?php

namespace App\Entity;

use App\Repository\PacientSampleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientSampleRepository::class)
 * @ORM\Table(name="pacients__recoltari")
 */
class PacientSample
{
    
    const STATUS_REQUESTED = 'solicitat de medic';
    const STATUS_REJECTED = 'respins de familie';
    const STATUS_APPROVED = 'aprobat de familie';
    const STATUS_PENDING = 'recoltat';
    const STATUS_SENT = 'trimis la laborator';
    const STATUS_DONE = 'analiza finalizata';
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $uid;

    /**
     * @ORM\ManyToOne(targetEntity=Pacient::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $pacient;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $addedBy;

    /**
     * @ORM\Column(type="datetime")
     */
    private $sampleDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $contactedFamily;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $collected;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function getPacient(): ?Pacient
    {
        return $this->pacient;
    }

    public function setPacient(?Pacient $pacient): self
    {
        $this->pacient = $pacient;

        return $this;
    }

    public function getAddedBy(): ?User
    {
        return $this->addedBy;
    }

    public function setAddedBy(?User $addedBy): self
    {
        $this->addedBy = $addedBy;

        return $this;
    }

    public function getSampleDate(): ?\DateTimeInterface
    {
        return $this->sampleDate;
    }

    public function setSampleDate(\DateTimeInterface $sampleDate): self
    {
        $this->sampleDate = $sampleDate;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function isContactedFamily(): ?bool
    {
        return $this->contactedFamily;
    }

    public function setContactedFamily(bool $contactedFamily): self
    {
        $this->contactedFamily = $contactedFamily;

        return $this;
    }

    public function isCollected(): ?bool
    {
        return $this->collected;
    }

    public function setCollected(bool $collected): self
    {
        $this->collected = $collected;

        return $this;
    }
    
    public function getStatuses() 
    {
        return [
            self::STATUS_REQUESTED => self::STATUS_REQUESTED,
            self::STATUS_REJECTED => self::STATUS_REJECTED,
            self::STATUS_APPROVED => self::STATUS_APPROVED,
            self::STATUS_PENDING => self::STATUS_PENDING,
            self::STATUS_SENT => self::STATUS_SENT,
            self::STATUS_DONE => self::STATUS_DONE
        ];
    }
    
    public function getBadgeClass()
    {
        $class = 'success';
        switch ($this->status) {
            case self::STATUS_REJECTED:
                $class = 'danger';
                break;
            case self::STATUS_REQUESTED:
            case self::STATUS_APPROVED:
            case self::STATUS_PENDING:
            case self::STATUS_SENT:
                $class = 'warning';
                break;
            case self::STATUS_DONE:
                $class = 'success';
                break;
            default:
                break;
        }

        return $class;
    }
}
