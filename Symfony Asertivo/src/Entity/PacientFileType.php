<?php

namespace App\Entity;

use App\Repository\PacientFileTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientFileTypeRepository::class)
 * @ORM\Table(name="files__pacients_type")
 */
class PacientFileType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=PacientFileGroup::class, inversedBy="pacientFileTypes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $fileGroup;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $requiredOnAdmission;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $requiredOnDischarge;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileGroup(): ?PacientFileGroup
    {
        return $this->fileGroup;
    }

    public function setFileGroup(?PacientFileGroup $fileGroup): self
    {
        $this->fileGroup = $fileGroup;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function isRequiredOnAdmission(): ?bool
    {
        return $this->requiredOnAdmission;
    }

    public function setRequiredOnAdmission(bool $requiredOnAdmission): self
    {
        $this->requiredOnAdmission = $requiredOnAdmission;

        return $this;
    }

    public function isRequiredOnDischarge(): ?bool
    {
        return $this->requiredOnDischarge;
    }

    public function setRequiredOnDischarge(bool $requiredOnDischarge): self
    {
        $this->requiredOnDischarge = $requiredOnDischarge;

        return $this;
    }
}
