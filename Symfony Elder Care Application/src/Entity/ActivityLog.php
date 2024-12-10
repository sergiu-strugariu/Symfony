<?php

namespace App\Entity;

use App\Repository\ActivityLogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ActivityLogRepository::class)
 */
class ActivityLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $page;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=NursingHome::class)
     */
    private $nursingHome;

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

    public function getPage(): ?string
    {
        return $this->page;
    }

    public function setPage(string $page): self
    {
        $this->page = $page;

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

    public function getNursingHome(): ?NursingHome
    {
        return $this->nursingHome;
    }

    public function setNursingHome(?NursingHome $nursingHome): self
    {
        $this->nursingHome = $nursingHome;

        return $this;
    }
}
