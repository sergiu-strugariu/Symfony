<?php

namespace App\Entity;

use App\Repository\ProspectRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProspectRepository::class)
 * @ORM\Table(name="pacients__prospecti")
 */
class Prospect
{
    const STATUS_IN_PROGRESS = 'In lucru';
    const STATUS_PENDING = 'Lista asteptare';
    const STATUS_REJECTED = 'Oferta refuzata';
    const STATUS_ACCEPTED = 'Oferta acceptata';
    const STATUS_ARCHIVED = 'Oferta arhivata';
    
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
     * @ORM\ManyToOne(targetEntity=NursingHome::class)
     */
    private $nursingHome;

    /**
     * @ORM\Column(name="nume_beneficiar", type="string", length=150, nullable=true)
     */
    private $beneficiaryName;

    /**
     * @ORM\Column(name="nume_apartinator", type="string", length=150, nullable=true)
     */
    private $relationName;

    /**
     * @ORM\Column(name="tip_camera", type="string", length=50, nullable=true)
     */
    private $roomType;

    /**
     * @ORM\Column(name="sex", type="string", length=20, nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(name="telefon", type="string", length=20, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(name="observatii", type="text", nullable=true)
     */
    private $observations;

    /**
     * @ORM\Column(name="vizionare_programata", type="datetime", nullable=true)
     */
    private $scheduledAt;

    /**
     * @ORM\Column(name="oferta_trimisa", type="datetime", nullable=true)
     */
    private $offerSentAt;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(name="sursa_lead", type="string", length=50, nullable=true)
     */
    private $leadSource;

    /**
     * @ORM\Column(name="sursa_lead_comment", type="text", nullable=true)
     */
    private $leadSourceComment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $addedBy;

    /**
     * @ORM\Column(name="varsta", type="integer", nullable=true)
     */
    private $age;

    /**
     * @ORM\Column(name="diagnostic", type="text", nullable=true)
     */
    private $diagnosis;

    /**
     * @ORM\Column(name="nevoi", type="text", nullable=true)
     */
    private $personalNeeds;

    /**
     * @ORM\Column(name="personalitate", type="text", nullable=true)
     */
    private $personality;

    /**
     * @ORM\Column(name="pampers", type="boolean", nullable=true)
     */
    private $requiresDiapers;

    /**
     * @ORM\Column(name="ingrijire_pat_medical", type="boolean", nullable=true)
     */
    private $requiresMedicalBed;

    /**
     * @ORM\Column(name="tratament", type="text", nullable=true)
     */
    private $treatment;

    /**
     * @ORM\Column(name="data_posibila_internare", type="datetime", nullable=true)
     */
    private $estimatedAdmissionDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $currentAddress;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $cancelReason;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default": 0})
     */
    private $canceled = false;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $offerPrice;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default": 0})
     */
    private $onboarded;

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

    public function getBeneficiaryName(): ?string
    {
        return $this->beneficiaryName;
    }

    public function setBeneficiaryName(?string $beneficiaryName): self
    {
        $this->beneficiaryName = $beneficiaryName;

        return $this;
    }

    public function getRelationName(): ?string
    {
        return $this->relationName;
    }

    public function setRelationName(?string $relationName): self
    {
        $this->relationName = $relationName;

        return $this;
    }

    public function getRoomType(): ?string
    {
        return $this->roomType;
    }

    public function setRoomType(?string $roomType): self
    {
        $this->roomType = $roomType;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): self
    {
        $this->observations = $observations;

        return $this;
    }

    public function getScheduledAt(): ?\DateTimeInterface
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(?\DateTimeInterface $scheduledAt): self
    {
        $this->scheduledAt = $scheduledAt;

        return $this;
    }

    public function getOfferSentAt(): ?\DateTimeInterface
    {
        return $this->offerSentAt;
    }

    public function setOfferSentAt(?\DateTimeInterface $offerSentAt): self
    {
        $this->offerSentAt = $offerSentAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLeadSource(): ?string
    {
        return $this->leadSource;
    }

    public function setLeadSource(?string $leadSource): self
    {
        $this->leadSource = $leadSource;

        return $this;
    }

    public function getLeadSourceComment(): ?string
    {
        return $this->leadSourceComment;
    }

    public function setLeadSourceComment(?string $leadSourceComment): self
    {
        $this->leadSourceComment = $leadSourceComment;

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

    public function getAddedBy(): ?User
    {
        return $this->addedBy;
    }

    public function setAddedBy(?User $addedBy): self
    {
        $this->addedBy = $addedBy;

        return $this;
    }

    public function getNursingHome(): ?NursingHome
    {
        return $this->nursingHome;
    }

    public function setNursingHome(?NursingHome $nursingHome): self
    {
        $this->nursingHome = $nursingHome;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getDiagnosis(): ?string
    {
        return $this->diagnosis;
    }

    public function setDiagnosis(?string $diagnosis): self
    {
        $this->diagnosis = $diagnosis;

        return $this;
    }

    public function getPersonalNeeds(): ?string
    {
        return $this->personalNeeds;
    }

    public function setPersonalNeeds(?string $personalNeeds): self
    {
        $this->personalNeeds = $personalNeeds;

        return $this;
    }

    public function getPersonality(): ?string
    {
        return $this->personality;
    }

    public function setPersonality(?string $personality): self
    {
        $this->personality = $personality;

        return $this;
    }

    public function isRequiresDiapers(): ?bool
    {
        return $this->requiresDiapers;
    }

    public function setRequiresDiapers(?bool $requiresDiapers): self
    {
        $this->requiresDiapers = $requiresDiapers;

        return $this;
    }

    public function isRequiresMedicalBed(): ?bool
    {
        return $this->requiresMedicalBed;
    }

    public function setRequiresMedicalBed(?bool $requiresMedicalBed): self
    {
        $this->requiresMedicalBed = $requiresMedicalBed;

        return $this;
    }

    public function getTreatment(): ?string
    {
        return $this->treatment;
    }

    public function setTreatment(?string $treatment): self
    {
        $this->treatment = $treatment;

        return $this;
    }

    public function getEstimatedAdmissionDate(): ?\DateTimeInterface
    {
        return $this->estimatedAdmissionDate;
    }

    public function setEstimatedAdmissionDate(?\DateTimeInterface $estimatedAdmissionDate): self
    {
        $this->estimatedAdmissionDate = $estimatedAdmissionDate;

        return $this;
    }

    public function getCurrentAddress(): ?string
    {
        return $this->currentAddress;
    }

    public function setCurrentAddress(?string $currentAddress): self
    {
        $this->currentAddress = $currentAddress;

        return $this;
    }

    public function getCancelReason(): ?string
    {
        return $this->cancelReason;
    }

    public function setCancelReason(?string $cancelReason): self
    {
        $this->cancelReason = $cancelReason;

        return $this;
    }

    public function isCanceled(): ?bool
    {
        return $this->canceled;
    }

    public function setCanceled(?bool $canceled): self
    {
        $this->canceled = $canceled;

        return $this;
    }

    public function getOfferPrice(): ?string
    {
        return $this->offerPrice;
    }

    public function setOfferPrice(?string $offerPrice): self
    {
        $this->offerPrice = $offerPrice;

        return $this;
    }

    public function isOnboarded(): ?bool
    {
        return $this->onboarded;
    }

    public function setOnboarded(?bool $onboarded): self
    {
        $this->onboarded = $onboarded;

        return $this;
    }
}
