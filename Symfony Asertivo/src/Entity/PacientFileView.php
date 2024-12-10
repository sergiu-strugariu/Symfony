<?php

namespace App\Entity;

use App\Repository\PacientFileViewRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientFileViewRepository::class)
 * @ORM\Table(name="files__view_documents")
 */
class PacientFileView
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="id_user", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=PacientFile::class, inversedBy="pacientFileViews")
     * @ORM\JoinColumn(name="id_document", nullable=false)
     */
    private $file;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFile(): ?PacientFile
    {
        return $this->file;
    }

    public function setFile(?PacientFile $file): self
    {
        $this->file = $file;

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
