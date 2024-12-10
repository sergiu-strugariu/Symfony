<?php

namespace App\Entity;

use App\Repository\PacientCommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientCommentRepository::class)
 * @ORM\Table(name="pacients__comentarii")
 */
class PacientComment
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
     * @ORM\ManyToOne(targetEntity=Pacient::class, inversedBy="pacientComments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pacient;
   
    /**
     * @ORM\OneToMany(targetEntity=PacientCommentFile::class, mappedBy="pacientComment", cascade={"remove"})
     */
    private $pacientCommentFiles;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $addedBy;

    /**
     * @ORM\Column(type="text")
     */
    private $commentBody;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->pacientCommentFiles = new ArrayCollection();
    }

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

    public function getAddedBy(): ?User
    {
        return $this->addedBy;
    }

    public function setAddedBy(?User $addedBy): self
    {
        $this->addedBy = $addedBy;

        return $this;
    }

    public function getCommentBody(): ?string
    {
        return $this->commentBody;
    }

    public function setCommentBody(string $commentBody): self
    {
        $this->commentBody = $commentBody;

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
     * @return Collection<int, PacientCommentFile>
     */
    public function getPacientCommentFiles(): Collection
    {
        return $this->pacientCommentFiles;
    }

    public function addPacientCommentFile(PacientCommentFile $pacientCommentFile): self
    {
        if (!$this->pacientCommentFiles->contains($pacientCommentFile)) {
            $this->pacientCommentFiles[] = $pacientCommentFile;
            $pacientCommentFile->setPacientComment($this);
        }

        return $this;
    }

    public function removePacientCommentFile(PacientCommentFile $pacientCommentFile): self
    {
        if ($this->pacientCommentFiles->removeElement($pacientCommentFile)) {
            // set the owning side to null (unless already changed)
            if ($pacientCommentFile->getPacientComment() === $this) {
                $pacientCommentFile->setPacientComment(null);
            }
        }

        return $this;
    }
    
    public function getFormattedCreatedAt($format): ?string
    {
        return $this->createdAt->format($format);
    }
}
