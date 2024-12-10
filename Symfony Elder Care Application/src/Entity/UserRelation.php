<?php

namespace App\Entity;

use App\Repository\UserRelationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRelationRepository::class)
 * @ORM\Table(name="pacients_apartinatori")
 */
class UserRelation
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
     * @ORM\ManyToOne(targetEntity=Pacient::class, inversedBy="pacientRelations")
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="pacients")
     * @ORM\JoinColumn(name="id_apartinator", nullable=false)
     */
    private $pacientRelation;

    /**
     * @ORM\Column(type="datetime")
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

    public function getPacientRelation(): ?User
    {
        return $this->pacientRelation;
    }

    public function setPacientRelation(?User $pacientRelation): self
    {
        $this->pacientRelation = $pacientRelation;

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
