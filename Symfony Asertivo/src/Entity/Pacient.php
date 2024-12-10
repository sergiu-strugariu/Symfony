<?php

namespace App\Entity;

use App\Repository\PacientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientRepository::class)
 */
class Pacient
{

    const STATUS_ADMITTED = 'internat';
    const STATUS_DISCHARGED = 'externat';
    const STATUS_PENDING = 'in procesare';
    const STATUS_ARCHIVED = 'arhivat';

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
     * @ORM\ManyToOne(targetEntity=NursingHome::class, inversedBy="pacients")
     * @ORM\JoinColumn(nullable=false)
     */
    private $nursingHome;

    /**
     * @ORM\ManyToOne(targetEntity=NursingHomeRoom::class)
     */
    private $nursingHomeRoom;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $treatmentPlanAuditedBy;

    /**
     * @ORM\ManyToOne(targetEntity=City::class)
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity=County::class)
     */
    private $county;

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $cnp;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $idCardSeries;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $idCardNumber;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateOfBirth;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $homeAddress;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $citizenship;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $treatmentPlanAuditDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $treatmentPlanAuditObservations;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default": 0})
     */
    private $treatmentPlanValid;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\OneToMany(targetEntity=UserRelation::class, mappedBy="pacient")
     */
    private $pacientRelations;

    /**
     * @ORM\OneToMany(targetEntity=PacientAdmission::class, mappedBy="pacient")
     */
    private $pacientAdmissions;

    /**
     * @ORM\OneToMany(targetEntity=PacientDischarge::class, mappedBy="pacient")
     */
    private $pacientDischarges;

    /**
     * @ORM\OneToMany(targetEntity=PacientGeneralData::class, mappedBy="pacient")
     */
    private $pacientGeneralData;

    /**
     * @ORM\OneToMany(targetEntity=PacientMedicalData::class, mappedBy="pacient")
     */
    private $pacientMedicalData;

    /**
     * @ORM\OneToMany(targetEntity=PacientClinicalExam::class, mappedBy="pacient")
     */
    private $pacientClinicalExams;

    /**
     * @ORM\OneToMany(targetEntity=PacientFile::class, mappedBy="pacient")
     */
    private $pacientFiles;

    /**
     * @ORM\OneToMany(targetEntity=PacientDiagnosis::class, mappedBy="pacient")
     */
    private $pacientDiagnoses;

    /**
     * @ORM\OneToMany(targetEntity=PacientMedication::class, mappedBy="pacient")
     */
    private $pacientMedications;

    /**
     * @ORM\OneToMany(targetEntity=PacientMedicationDetails::class, mappedBy="pacient")
     */
    private $pacientMedicationDetails;

    /**
     * @ORM\OneToMany(targetEntity=PacientMonitoringMedical::class, mappedBy="pacient")
     */
    private $pacientMonitoringMedicals;

    /**
     * @ORM\OneToMany(targetEntity=PacientComment::class, mappedBy="pacient")
     */
    private $pacientComments;

    public function __construct()
    {
        $this->pacientRelations = new ArrayCollection();
        $this->pacientAdmissions = new ArrayCollection();
        $this->pacientDischarges = new ArrayCollection();
        $this->pacientGeneralData = new ArrayCollection();
        $this->pacientMedicalData = new ArrayCollection();
        $this->pacientClinicalExams = new ArrayCollection();
        $this->pacientFiles = new ArrayCollection();
        $this->pacientDiagnoses = new ArrayCollection();
        $this->pacientMedications = new ArrayCollection();
        $this->pacientMedicationDetails = new ArrayCollection();
        $this->pacientMonitoringMedicals = new ArrayCollection();
        $this->pacientComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

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

    public function getNursingHome(): ?NursingHome
    {
        return $this->nursingHome;
    }

    public function setNursingHome(?NursingHome $nursingHome): self
    {
        $this->nursingHome = $nursingHome;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCounty(): ?County
    {
        return $this->county;
    }

    public function setCounty(?County $county): self
    {
        $this->county = $county;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getCnp(): ?string
    {
        return $this->cnp;
    }

    public function setCnp(?string $cnp): self
    {
        $this->cnp = $cnp;

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

    public function getIdCardSeries(): ?string
    {
        return $this->idCardSeries;
    }

    public function setIdCardSeries(?string $idCardSeries): self
    {
        $this->idCardSeries = $idCardSeries;

        return $this;
    }

    public function getIdCardNumber(): ?int
    {
        return $this->idCardNumber;
    }

    public function setIdCardNumber(?int $idCardNumber): self
    {
        $this->idCardNumber = $idCardNumber;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getHomeAddress(): ?string
    {
        return $this->homeAddress;
    }

    public function setHomeAddress(?string $homeAddress): self
    {
        $this->homeAddress = $homeAddress;

        return $this;
    }

    public function getCitizenship(): ?string
    {
        return $this->citizenship;
    }

    public function setCitizenship(?string $citizenship): self
    {
        $this->citizenship = $citizenship;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

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

    public function getNursingHomeRoom(): ?NursingHomeRoom
    {
        return $this->nursingHomeRoom;
    }

    public function setNursingHomeRoom(?NursingHomeRoom $nursingHomeRoom): self
    {
        $this->nursingHomeRoom = $nursingHomeRoom;

        return $this;
    }

    public function getTreatmentPlanAuditedBy(): ?User
    {
        return $this->treatmentPlanAuditedBy;
    }

    public function setTreatmentPlanAuditedBy(?User $treatmentPlanAuditedBy): self
    {
        $this->treatmentPlanAuditedBy = $treatmentPlanAuditedBy;

        return $this;
    }

    public function getTreatmentPlanAuditDate(): ?\DateTimeInterface
    {
        return $this->treatmentPlanAuditDate;
    }

    public function setTreatmentPlanAuditDate(?\DateTimeInterface $treatmentPlanAuditDate): self
    {
        $this->treatmentPlanAuditDate = $treatmentPlanAuditDate;

        return $this;
    }

    public function getTreatmentPlanAuditObservations(): ?string
    {
        return $this->treatmentPlanAuditObservations;
    }

    public function setTreatmentPlanAuditObservations(?string $treatmentPlanAuditObservations): self
    {
        $this->treatmentPlanAuditObservations = $treatmentPlanAuditObservations;

        return $this;
    }

    public function isTreatmentPlanValid(): ?bool
    {
        return $this->treatmentPlanValid;
    }

    public function setTreatmentPlanValid(?bool $treatmentPlanValid): self
    {
        $this->treatmentPlanValid = $treatmentPlanValid;

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

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getName()
    {
        if (empty($this->firstName) && empty($this->lastName)) {
            return 'Fara nume';
        }

        return $this->lastName . ' ' . $this->firstName;
    }

    public function getPhotoPath()
    {
        $package = new UrlPackage(User::CLOUDFLARE_R2_FILE_PATH, new EmptyVersionStrategy());
        return (empty($this->photo)) ? $package->getUrl('/assets/media/avatars/blank.png') : $package->getUrl(sprintf('/%s/%s', 'uploads', $this->photo));
    }

    /**
     * @return Collection<int, PacientAdmission>
     */
    public function getPacientAdmissions(): Collection
    {
        return $this->pacientAdmissions;
    }

    public function addPacientAdmission(PacientAdmission $pacientAdmission): self
    {
        if (!$this->pacientAdmissions->contains($pacientAdmission)) {
            $this->pacientAdmissions[] = $pacientAdmission;
            $pacientAdmission->setPacient($this);
        }

        return $this;
    }

    public function removePacientAdmission(PacientAdmission $pacientAdmission): self
    {
        if ($this->pacientAdmissions->removeElement($pacientAdmission)) {
            // set the owning side to null (unless already changed)
            if ($pacientAdmission->getPacient() === $this) {
                $pacientAdmission->setPacient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacientDischarge>
     */
    public function getPacientDischarges(): Collection
    {
        return $this->pacientDischarges;
    }

    public function addPacientDischarge(PacientDischarge $pacientDischarge): self
    {
        if (!$this->pacientDischarges->contains($pacientDischarge)) {
            $this->pacientDischarges[] = $pacientDischarge;
            $pacientDischarge->setPacient($this);
        }

        return $this;
    }

    public function removePacientDischarge(PacientDischarge $pacientDischarge): self
    {
        if ($this->pacientDischarges->removeElement($pacientDischarge)) {
            // set the owning side to null (unless already changed)
            if ($pacientDischarge->getPacient() === $this) {
                $pacientDischarge->setPacient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacientGeneralData>
     */
    public function getPacientGeneralData(): Collection
    {
        return $this->pacientGeneralData;
    }

    public function addPacientGeneralData(PacientGeneralData $pacientGeneralData): self
    {
        if (!$this->pacientGeneralData->contains($pacientGeneralData)) {
            $this->pacientGeneralData[] = $pacientGeneralData;
            $pacientGeneralData->setPacient($this);
        }

        return $this;
    }

    public function removePacientGeneralData(PacientGeneralData $pacientGeneralData): self
    {
        if ($this->pacientGeneralData->removeElement($pacientGeneralData)) {
            // set the owning side to null (unless already changed)
            if ($pacientGeneralData->getPacient() === $this) {
                $pacientGeneralData->setPacient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacientMedicalData>
     */
    public function getPacientMedicalData(): Collection
    {
        return $this->pacientMedicalData;
    }

    public function addPacientMedicalData(PacientMedicalData $pacientMedicalData): self
    {
        if (!$this->pacientMedicalData->contains($pacientMedicalData)) {
            $this->pacientMedicalData[] = $pacientMedicalData;
            $pacientMedicalData->setPacient($this);
        }

        return $this;
    }

    public function removePacientMedicalData(PacientMedicalData $pacientMedicalData): self
    {
        if ($this->pacientMedicalData->removeElement($pacientMedicalData)) {
            // set the owning side to null (unless already changed)
            if ($pacientMedicalData->getPacient() === $this) {
                $pacientMedicalData->setPacient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacientClinicalExam>
     */
    public function getPacientClinicalExams(): Collection
    {
        return $this->pacientClinicalExams;
    }

    public function addPacientClinicalExam(PacientClinicalExam $pacientClinicalExam): self
    {
        if (!$this->pacientClinicalExams->contains($pacientClinicalExam)) {
            $this->pacientClinicalExams[] = $pacientClinicalExam;
            $pacientClinicalExam->setPacient($this);
        }

        return $this;
    }

    public function removePacientClinicalExam(PacientClinicalExam $pacientClinicalExam): self
    {
        if ($this->pacientClinicalExams->removeElement($pacientClinicalExam)) {
            // set the owning side to null (unless already changed)
            if ($pacientClinicalExam->getPacient() === $this) {
                $pacientClinicalExam->setPacient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacientFile>
     */
    public function getPacientFiles(): Collection
    {
        return $this->pacientFiles;
    }

    public function addPacientFile(PacientFile $pacientFile): self
    {
        if (!$this->pacientFiles->contains($pacientFile)) {
            $this->pacientFiles[] = $pacientFile;
            $pacientFile->setPacient($this);
        }

        return $this;
    }

    public function removePacientFile(PacientFile $pacientFile): self
    {
        if ($this->pacientFiles->removeElement($pacientFile)) {
            // set the owning side to null (unless already changed)
            if ($pacientFile->getPacient() === $this) {
                $pacientFile->setPacient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacientDiagnosis>
     */
    public function getPacientDiagnoses(): Collection
    {
        return $this->pacientDiagnoses;
    }

    public function addPacientDiagnosis(PacientDiagnosis $pacientDiagnosis): self
    {
        if (!$this->pacientDiagnoses->contains($pacientDiagnosis)) {
            $this->pacientDiagnoses[] = $pacientDiagnosis;
            $pacientDiagnosis->setPacient($this);
        }

        return $this;
    }

    public function removePacientDiagnosis(PacientDiagnosis $pacientDiagnosis): self
    {
        if ($this->pacientDiagnoses->removeElement($pacientDiagnosis)) {
            // set the owning side to null (unless already changed)
            if ($pacientDiagnosis->getPacient() === $this) {
                $pacientDiagnosis->setPacient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacientMedication>
     */
    public function getPacientMedications(): Collection
    {
        return $this->pacientMedications;
    }

    public function addPacientMedication(PacientMedication $pacientMedication): self
    {
        if (!$this->pacientMedications->contains($pacientMedication)) {
            $this->pacientMedications[] = $pacientMedication;
            $pacientMedication->setPacient($this);
        }

        return $this;
    }

    public function removePacientMedication(PacientMedication $pacientMedication): self
    {
        if ($this->pacientMedications->removeElement($pacientMedication)) {
            // set the owning side to null (unless already changed)
            if ($pacientMedication->getPacient() === $this) {
                $pacientMedication->setPacient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacientMedicationDetails>
     */
    public function getPacientMedicationDetails(): Collection
    {
        return $this->pacientMedicationDetails;
    }

    public function addPacientMedicationDetail(PacientMedicationDetails $pacientMedicationDetail): self
    {
        if (!$this->pacientMedicationDetails->contains($pacientMedicationDetail)) {
            $this->pacientMedicationDetails[] = $pacientMedicationDetail;
            $pacientMedicationDetail->setPacient($this);
        }

        return $this;
    }

    public function removePacientMedicationDetail(PacientMedicationDetails $pacientMedicationDetail): self
    {
        if ($this->pacientMedicationDetails->removeElement($pacientMedicationDetail)) {
            // set the owning side to null (unless already changed)
            if ($pacientMedicationDetail->getPacient() === $this) {
                $pacientMedicationDetail->setPacient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacientMonitoringMedical>
     */
    public function getPacientMonitoringMedicals(): Collection
    {
        return $this->pacientMonitoringMedicals;
    }

    public function addPacientMonitoringMedical(PacientMonitoringMedical $pacientMonitoringMedical): self
    {
        if (!$this->pacientMonitoringMedicals->contains($pacientMonitoringMedical)) {
            $this->pacientMonitoringMedicals[] = $pacientMonitoringMedical;
            $pacientMonitoringMedical->setPacient($this);
        }

        return $this;
    }

    public function removePacientMonitoringMedical(PacientMonitoringMedical $pacientMonitoringMedical): self
    {
        if ($this->pacientMonitoringMedicals->removeElement($pacientMonitoringMedical)) {
            // set the owning side to null (unless already changed)
            if ($pacientMonitoringMedical->getPacient() === $this) {
                $pacientMonitoringMedical->setPacient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacientComment>
     */
    public function getPacientComments(): Collection
    {
        return $this->pacientComments;
    }

    public function addPacientComment(PacientComment $pacientComment): self
    {
        if (!$this->pacientComments->contains($pacientComment)) {
            $this->pacientComments[] = $pacientComment;
            $pacientComment->setPacient($this);
        }

        return $this;
    }

    public function removePacientComment(PacientComment $pacientComment): self
    {
        if ($this->pacientComments->removeElement($pacientComment)) {
            // set the owning side to null (unless already changed)
            if ($pacientComment->getPacient() === $this) {
                $pacientComment->setPacient(null);
            }
        }

        return $this;
    }

    static function getPacientStatuses()
    {
        return [
            self::STATUS_ADMITTED => 'Internat',
            self::STATUS_DISCHARGED => 'Externat',
            self::STATUS_PENDING => 'In procesare',
            self::STATUS_ARCHIVED => 'Arhivat'
        ];
    }

    static function getPacientStatusClass($status)
    {
        $class = '';

        switch ($status) {
            case self::STATUS_ADMITTED:
                $class = 'success';
                break;
            case self::STATUS_DISCHARGED:
                $class = 'danger';
                break;
            case self::STATUS_PENDING:
                $class = 'light';
                break;
            case self::STATUS_ARCHIVED:
                $class = 'warning';
                break;
            default:
                break;
        }

        return $class;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->lastName . ' ' . $this->firstName;
    }
}
