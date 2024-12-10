<?php

namespace App\Entity;

use App\Repository\PacientPhysicalDataRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientPhysicalDataRepository::class)
 * @ORM\Table(name="pacients__date_kinetoterapie")
 */
class PacientPhysicalData
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

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
     * @ORM\Column(type="string", length=40, unique=true)
     */
    private $uid;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $massage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $therapeuticMassage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $tappingMassage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $bodyRepositioningImmobilizedPatients;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $correctingBodyPostureAndAlignment;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $increasingBodyCoordinationAndBalance;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $increasingJointMobility;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $increasingJointMobilityPassive;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $increasingJointMobilityActive;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $increasingJointMobilityPassiveActive;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $increasingJointMobilityActiveVoluntary;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $increasingJointMobilityAutoPassive;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $increasingMuscleStrengthAndEndurance;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $scriptotherapy;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $rocherCage;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $multifunctionalDevice;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $stretching;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $walkingExercises;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $walkingExercisesSteps;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $walkingExercisesSupport;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $walkingExercisesBicycle;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $walkingExercisesWalkingLane;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $groupExercises;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $groupExercisesJointMobility;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $groupExercisesBodyBalanceAndCoordination;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $groupExercisesResistanceAndMuscleStrength;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $trellisExercises;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $observations;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\Column(name="administration_date", type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $refusal;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $medicalProblem;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(string $uid): self
    {
        $this->uid = $uid;

        return $this;
    }

    public function isMassage(): ?bool
    {
        return $this->massage;
    }

    public function setMassage(?bool $massage): self
    {
        $this->massage = $massage;

        return $this;
    }

    public function isTherapeuticMassage(): ?bool
    {
        return $this->therapeuticMassage;
    }

    public function setTherapeuticMassage(?bool $therapeuticMassage): self
    {
        $this->therapeuticMassage = $therapeuticMassage;

        return $this;
    }

    public function isTappingMassage(): ?bool
    {
        return $this->tappingMassage;
    }

    public function setTappingMassage(?bool $tappingMassage): self
    {
        $this->tappingMassage = $tappingMassage;

        return $this;
    }

    public function isBodyRepositioningImmobilizedPatients(): ?bool
    {
        return $this->bodyRepositioningImmobilizedPatients;
    }

    public function setBodyRepositioningImmobilizedPatients(?bool $bodyRepositioningImmobilizedPatients): self
    {
        $this->bodyRepositioningImmobilizedPatients = $bodyRepositioningImmobilizedPatients;

        return $this;
    }

    public function isCorrectingBodyPostureAndAlignment(): ?bool
    {
        return $this->correctingBodyPostureAndAlignment;
    }

    public function setCorrectingBodyPostureAndAlignment(?bool $correctingBodyPostureAndAlignment): self
    {
        $this->correctingBodyPostureAndAlignment = $correctingBodyPostureAndAlignment;

        return $this;
    }

    public function isIncreasingBodyCoordinationAndBalance(): ?bool
    {
        return $this->increasingBodyCoordinationAndBalance;
    }

    public function setIncreasingBodyCoordinationAndBalance(?bool $increasingBodyCoordinationAndBalance): self
    {
        $this->increasingBodyCoordinationAndBalance = $increasingBodyCoordinationAndBalance;

        return $this;
    }

    public function isIncreasingJointMobility(): ?bool
    {
        return $this->increasingJointMobility;
    }

    public function setIncreasingJointMobility(?bool $increasingJointMobility): self
    {
        $this->increasingJointMobility = $increasingJointMobility;

        return $this;
    }

    public function isIncreasingJointMobilityPassive(): ?bool
    {
        return $this->increasingJointMobilityPassive;
    }

    public function setIncreasingJointMobilityPassive(?bool $increasingJointMobilityPassive): self
    {
        $this->increasingJointMobilityPassive = $increasingJointMobilityPassive;

        return $this;
    }

    public function isIncreasingJointMobilityActive(): ?bool
    {
        return $this->increasingJointMobilityActive;
    }

    public function setIncreasingJointMobilityActive(?bool $increasingJointMobilityActive): self
    {
        $this->increasingJointMobilityActive = $increasingJointMobilityActive;

        return $this;
    }

    public function isIncreasingJointMobilityPassiveActive(): ?bool
    {
        return $this->increasingJointMobilityPassiveActive;
    }

    public function setIncreasingJointMobilityPassiveActive(?bool $increasingJointMobilityPassiveActive): self
    {
        $this->increasingJointMobilityPassiveActive = $increasingJointMobilityPassiveActive;

        return $this;
    }

    public function isIncreasingJointMobilityActiveVoluntary(): ?bool
    {
        return $this->increasingJointMobilityActiveVoluntary;
    }

    public function setIncreasingJointMobilityActiveVoluntary(?bool $increasingJointMobilityActiveVoluntary): self
    {
        $this->increasingJointMobilityActiveVoluntary = $increasingJointMobilityActiveVoluntary;

        return $this;
    }

    public function isIncreasingJointMobilityAutoPassive(): ?bool
    {
        return $this->increasingJointMobilityAutoPassive;
    }

    public function setIncreasingJointMobilityAutoPassive(?bool $increasingJointMobilityAutoPassive): self
    {
        $this->increasingJointMobilityAutoPassive = $increasingJointMobilityAutoPassive;

        return $this;
    }

    public function isIncreasingMuscleStrengthAndEndurance(): ?bool
    {
        return $this->increasingMuscleStrengthAndEndurance;
    }

    public function setIncreasingMuscleStrengthAndEndurance(?bool $increasingMuscleStrengthAndEndurance): self
    {
        $this->increasingMuscleStrengthAndEndurance = $increasingMuscleStrengthAndEndurance;

        return $this;
    }

    public function isScriptotherapy(): ?bool
    {
        return $this->scriptotherapy;
    }

    public function setScriptotherapy(?bool $scriptotherapy): self
    {
        $this->scriptotherapy = $scriptotherapy;

        return $this;
    }

    public function isRocherCage(): ?bool
    {
        return $this->rocherCage;
    }

    public function setRocherCage(?bool $rocherCage): self
    {
        $this->rocherCage = $rocherCage;

        return $this;
    }

    public function isMultifunctionalDevice(): ?bool
    {
        return $this->multifunctionalDevice;
    }

    public function setMultifunctionalDevice(?bool $multifunctionalDevice): self
    {
        $this->multifunctionalDevice = $multifunctionalDevice;

        return $this;
    }

    public function isStretching(): ?bool
    {
        return $this->stretching;
    }

    public function setStretching(?bool $stretching): self
    {
        $this->stretching = $stretching;

        return $this;
    }

    public function isWalkingExercises(): ?bool
    {
        return $this->walkingExercises;
    }

    public function setWalkingExercises(?bool $walkingExercises): self
    {
        $this->walkingExercises = $walkingExercises;

        return $this;
    }

    public function isWalkingExercisesSteps(): ?bool
    {
        return $this->walkingExercisesSteps;
    }

    public function setWalkingExercisesSteps(?bool $walkingExercisesSteps): self
    {
        $this->walkingExercisesSteps = $walkingExercisesSteps;

        return $this;
    }

    public function isWalkingExercisesSupport(): ?bool
    {
        return $this->walkingExercisesSupport;
    }

    public function setWalkingExercisesSupport(?bool $walkingExercisesSupport): self
    {
        $this->walkingExercisesSupport = $walkingExercisesSupport;

        return $this;
    }

    public function isWalkingExercisesBicycle(): ?bool
    {
        return $this->walkingExercisesBicycle;
    }

    public function setWalkingExercisesBicycle(?bool $walkingExercisesBicycle): self
    {
        $this->walkingExercisesBicycle = $walkingExercisesBicycle;

        return $this;
    }

    public function isWalkingExercisesWalkingLane(): ?bool
    {
        return $this->walkingExercisesWalkingLane;
    }

    public function setWalkingExercisesWalkingLane(?bool $walkingExercisesWalkingLane): self
    {
        $this->walkingExercisesWalkingLane = $walkingExercisesWalkingLane;

        return $this;
    }

    public function isGroupExercises(): ?bool
    {
        return $this->groupExercises;
    }

    public function setGroupExercises(?bool $groupExercises): self
    {
        $this->groupExercises = $groupExercises;

        return $this;
    }

    public function isGroupExercisesJointMobility(): ?bool
    {
        return $this->groupExercisesJointMobility;
    }

    public function setGroupExercisesJointMobility(?bool $groupExercisesJointMobility): self
    {
        $this->groupExercisesJointMobility = $groupExercisesJointMobility;

        return $this;
    }

    public function isGroupExercisesBodyBalanceAndCoordination(): ?bool
    {
        return $this->groupExercisesBodyBalanceAndCoordination;
    }

    public function setGroupExercisesBodyBalanceAndCoordination(?bool $groupExercisesBodyBalanceAndCoordination): self
    {
        $this->groupExercisesBodyBalanceAndCoordination = $groupExercisesBodyBalanceAndCoordination;

        return $this;
    }

    public function isGroupExercisesResistanceAndMuscleStrength(): ?bool
    {
        return $this->groupExercisesResistanceAndMuscleStrength;
    }

    public function setGroupExercisesResistanceAndMuscleStrength(?bool $groupExercisesResistanceAndMuscleStrength): self
    {
        $this->groupExercisesResistanceAndMuscleStrength = $groupExercisesResistanceAndMuscleStrength;

        return $this;
    }

    public function isTrellisExercises(): ?bool
    {
        return $this->trellisExercises;
    }

    public function setTrellisExercises(?bool $trellisExercises): self
    {
        $this->trellisExercises = $trellisExercises;

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

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function isRefusal(): ?bool
    {
        return $this->refusal;
    }

    public function setRefusal(?bool $refusal): self
    {
        $this->refusal = $refusal;

        return $this;
    }

    public function isMedicalProblem(): ?bool
    {
        return $this->medicalProblem;
    }

    public function setMedicalProblem(?bool $medicalProblem): self
    {
        $this->medicalProblem = $medicalProblem;

        return $this;
    }
}
