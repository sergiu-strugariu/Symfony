<?php

namespace App\Entity;

use App\Repository\PacientCommentFileRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientCommentFileRepository::class)
 * @ORM\Table(name="pacients__comentarii_fisiere")
 */
class PacientCommentFile
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity=PacientComment::class, inversedBy="pacientCommentFiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pacientComment;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fileName;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPacientComment(): ?PacientComment
    {
        return $this->pacientComment;
    }

    public function setPacientComment(?PacientComment $pacientComment): self
    {
        $this->pacientComment = $pacientComment;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }
}
