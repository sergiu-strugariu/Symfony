<?php

namespace App\Entity;

use App\Repository\PacientRoomRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientRoomRepository::class)
 * @ORM\Table(name="pacients_rooms_assigned")
 * 
 */
class PacientRoom
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
     * @ORM\ManyToOne(targetEntity=Pacient::class)
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;

    /**
     * @ORM\ManyToOne(targetEntity=NursingHomeRoom::class, inversedBy="pacients")
     * @ORM\JoinColumn(name="id_camera", nullable=false)
     */
    private $nursingHomeRoom;

    /**
     * @ORM\Column(name="observatii", type="text", nullable=true)
     */
    private $observations;

    /**
     * @ORM\Column(type="date")
     */
    private $createdAt;

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

    public function getNursingHomeRoom(): ?NursingHomeRoom
    {
        return $this->nursingHomeRoom;
    }

    public function setNursingHomeRoom(?NursingHomeRoom $nursingHomeRoom): self
    {
        $this->nursingHomeRoom = $nursingHomeRoom;

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
}
