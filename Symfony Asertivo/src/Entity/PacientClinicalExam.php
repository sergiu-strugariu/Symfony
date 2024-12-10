<?php

namespace App\Entity;

use App\Repository\PacientClinicalExamRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientClinicalExamRepository::class)
 * @ORM\Table(name="pacients__examen_clinic")
 */
class PacientClinicalExam
{
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
     * @ORM\ManyToOne(targetEntity=Pacient::class, inversedBy="pacientClinicalExams")
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;
    
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $addedBy;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $objectiveExamination;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $generalState;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $nutritionalStatus;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $stateOfConsciousness;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $skin;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $mucous;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $skinAppendages;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $connectiveAdiposeTissue;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $ganglionicSystem;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $muscularSystem;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $osteoArticularSystem;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $respiratoryApparatus;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $cardiovascularApparatus;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $digestiveSystem;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $liverSpleenBileDucts;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $uroGenitalApparatus;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $nervousSystemEndocrineSenseOrgans;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

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

    public function getObjectiveExamination(): ?string
    {
        return $this->objectiveExamination;
    }

    public function setObjectiveExamination(?string $objectiveExamination): self
    {
        $this->objectiveExamination = $objectiveExamination;

        return $this;
    }

    public function getGeneralState(): ?string
    {
        return $this->generalState;
    }

    public function setGeneralState(?string $generalState): self
    {
        $this->generalState = $generalState;

        return $this;
    }

    public function getNutritionalStatus(): ?string
    {
        return $this->nutritionalStatus;
    }

    public function setNutritionalStatus(?string $nutritionalStatus): self
    {
        $this->nutritionalStatus = $nutritionalStatus;

        return $this;
    }

    public function getStateOfConsciousness(): ?string
    {
        return $this->stateOfConsciousness;
    }

    public function setStateOfConsciousness(?string $stateOfConsciousness): self
    {
        $this->stateOfConsciousness = $stateOfConsciousness;

        return $this;
    }

    public function getSkin(): ?string
    {
        return $this->skin;
    }

    public function setSkin(?string $skin): self
    {
        $this->skin = $skin;

        return $this;
    }

    public function getMucous(): ?string
    {
        return $this->mucous;
    }

    public function setMucous(?string $mucous): self
    {
        $this->mucous = $mucous;

        return $this;
    }

    public function getSkinAppendages(): ?string
    {
        return $this->skinAppendages;
    }

    public function setSkinAppendages(?string $skinAppendages): self
    {
        $this->skinAppendages = $skinAppendages;

        return $this;
    }

    public function getConnectiveAdiposeTissue(): ?string
    {
        return $this->connectiveAdiposeTissue;
    }

    public function setConnectiveAdiposeTissue(?string $connectiveAdiposeTissue): self
    {
        $this->connectiveAdiposeTissue = $connectiveAdiposeTissue;

        return $this;
    }

    public function getGanglionicSystem(): ?string
    {
        return $this->ganglionicSystem;
    }

    public function setGanglionicSystem(?string $ganglionicSystem): self
    {
        $this->ganglionicSystem = $ganglionicSystem;

        return $this;
    }

    public function getMuscularSystem(): ?string
    {
        return $this->muscularSystem;
    }

    public function setMuscularSystem(?string $muscularSystem): self
    {
        $this->muscularSystem = $muscularSystem;

        return $this;
    }

    public function getOsteoArticularSystem(): ?string
    {
        return $this->osteoArticularSystem;
    }

    public function setOsteoArticularSystem(?string $osteoArticularSystem): self
    {
        $this->osteoArticularSystem = $osteoArticularSystem;

        return $this;
    }

    public function getRespiratoryApparatus(): ?string
    {
        return $this->respiratoryApparatus;
    }

    public function setRespiratoryApparatus(?string $respiratoryApparatus): self
    {
        $this->respiratoryApparatus = $respiratoryApparatus;

        return $this;
    }

    public function getCardiovascularApparatus(): ?string
    {
        return $this->cardiovascularApparatus;
    }

    public function setCardiovascularApparatus(?string $cardiovascularApparatus): self
    {
        $this->cardiovascularApparatus = $cardiovascularApparatus;

        return $this;
    }

    public function getDigestiveSystem(): ?string
    {
        return $this->digestiveSystem;
    }

    public function setDigestiveSystem(?string $digestiveSystem): self
    {
        $this->digestiveSystem = $digestiveSystem;

        return $this;
    }

    public function getLiverSpleenBileDucts(): ?string
    {
        return $this->liverSpleenBileDucts;
    }

    public function setLiverSpleenBileDucts(?string $liverSpleenBileDucts): self
    {
        $this->liverSpleenBileDucts = $liverSpleenBileDucts;

        return $this;
    }

    public function getUroGenitalApparatus(): ?string
    {
        return $this->uroGenitalApparatus;
    }

    public function setUroGenitalApparatus(?string $uroGenitalApparatus): self
    {
        $this->uroGenitalApparatus = $uroGenitalApparatus;

        return $this;
    }

    public function getNervousSystemEndocrineSenseOrgans(): ?string
    {
        return $this->nervousSystemEndocrineSenseOrgans;
    }

    public function setNervousSystemEndocrineSenseOrgans(?string $nervousSystemEndocrineSenseOrgans): self
    {
        $this->nervousSystemEndocrineSenseOrgans = $nervousSystemEndocrineSenseOrgans;

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

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
