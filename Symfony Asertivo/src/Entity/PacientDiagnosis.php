<?php

namespace App\Entity;

use App\Repository\PacientDiagnosisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientDiagnosisRepository::class)
 * @ORM\Table(name="pacients__diagnoses")
 */
class PacientDiagnosis
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Pacient::class, inversedBy="pacientDiagnoses")
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="id_medic", nullable=false)
     */
    private $medic;

    /**
     * @ORM\Column(name="diagnostic", type="string", length=500, nullable=true)
     */
    private $diagnosis;

    /**
     * @ORM\Column(name="observatii", type="text", nullable=true)
     */
    private $observations;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=PacientMedication::class, mappedBy="pacientDiagnosis")
     */
    private $pacientMedications;
    
    /**
     * @ORM\OneToMany(targetEntity=SummaryNotification::class, mappedBy="pacientDiagnosis")
     */
    private $notifications;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    public function __construct()
    {
        $this->pacientMedications = new ArrayCollection();
        $this->notifications = new ArrayCollection();
    }

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

    public function getMedic(): ?User
    {
        return $this->medic;
    }

    public function setMedic(?User $medic): self
    {
        $this->medic = $medic;

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
            $pacientMedication->setPacientDiagnosis($this);
        }

        return $this;
    }

    public function removePacientMedication(PacientMedication $pacientMedication): self
    {
        if ($this->pacientMedications->removeElement($pacientMedication)) {
            // set the owning side to null (unless already changed)
            if ($pacientMedication->getPacientDiagnosis() === $this) {
                $pacientMedication->setPacientDiagnosis(null);
            }
        }

        return $this;
    }
    
    /**
     * @return Collection<int, SummaryNotification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
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
