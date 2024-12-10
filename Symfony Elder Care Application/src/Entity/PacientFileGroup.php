<?php

namespace App\Entity;

use App\Repository\PacientFileGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientFileGroupRepository::class)
 * @ORM\Table(name="files__pacients_group")
 */
class PacientFileGroup
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=PacientFileType::class, mappedBy="fileGroup")
     */
    private $pacientFileTypes;

    public function __construct()
    {
        $this->pacientFileTypes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, PacientFileType>
     */
    public function getPacientFileTypes(): Collection
    {
        return $this->pacientFileTypes;
    }

    public function addPacientFileType(PacientFileType $pacientFileType): self
    {
        if (!$this->pacientFileTypes->contains($pacientFileType)) {
            $this->pacientFileTypes[] = $pacientFileType;
            $pacientFileType->setFileGroup($this);
        }

        return $this;
    }

    public function removePacientFileType(PacientFileType $pacientFileType): self
    {
        if ($this->pacientFileTypes->removeElement($pacientFileType)) {
            // set the owning side to null (unless already changed)
            if ($pacientFileType->getFileGroup() === $this) {
                $pacientFileType->setFileGroup(null);
            }
        }

        return $this;
    }
}
