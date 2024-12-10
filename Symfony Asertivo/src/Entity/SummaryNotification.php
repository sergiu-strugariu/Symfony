<?php

namespace App\Entity;

use App\Repository\SummaryNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SummaryNotificationRepository::class)
 * @ORM\Table(name="pacients__borderou_notificari")
 */
class SummaryNotification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Pacient::class)
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="created_by_user", nullable=false)
     */
    private $createdBy;

    /**
     * @ORM\Column(name="observatie", type="text", nullable=true)
     */
    private $observations;

    /**
     * @ORM\ManyToOne(targetEntity=PacientDiagnosis::class, inversedBy="notifications")
     * @ORM\JoinColumn(nullable=true)
     */
    private $pacientDiagnosis;

    /**
     * @ORM\Column(type="date")
     */
    private $notificationDate;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

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

    public function getPacientDiagnosis(): ?PacientDiagnosis
    {
        return $this->pacientDiagnosis;
    }

    public function setPacientDiagnosis(?PacientDiagnosis $pacientDiagnosis): self
    {
        $this->pacientDiagnosis = $pacientDiagnosis;

        return $this;
    }

    public function getNotificationDate(): ?\DateTimeInterface
    {
        return $this->notificationDate;
    }

    public function setNotificationDate(\DateTimeInterface $notificationDate): self
    {
        $this->notificationDate = $notificationDate;

        return $this;
    }
}
