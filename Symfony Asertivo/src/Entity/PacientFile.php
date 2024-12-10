<?php

namespace App\Entity;

use App\Repository\PacientFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PacientFileRepository::class)
 * @ORM\Table(name="files__pacients_list")
 */
class PacientFile
{
    
    const STATUS_UPLOADED = 'Incarcat';
    const STATUS_WAITING = 'In asteptare';
    
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
     * @ORM\ManyToOne(targetEntity=Pacient::class, inversedBy="pacientFiles")
     * @ORM\JoinColumn(name="id_pacient", nullable=false)
     */
    private $pacient;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="uploaded_by")
     */
    private $addedBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $uploadedAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="id_responsabil", nullable=false)
     */
    private $userResponsible;

    /**
     * @ORM\ManyToOne(targetEntity=PacientFileType::class)
     * @ORM\JoinColumn(name="id_file_type", nullable=true)
     */
    private $fileType;
    
    /**
     * @ORM\ManyToOne(targetEntity=PacientFileGroup::class)
     * @ORM\JoinColumn(name="id_type_parent", nullable=false)
     */
    private $fileGroup;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $fileName;
    
    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $filePath;
    
    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $fileExtension;

    /**
     * @ORM\Column(name="nr", type="string", length=20, nullable=true)
     */
    private $fileNumber;
    
    /**
     * @ORM\Column(name="data", type="date", nullable=true)
     */
    private $fileDate;

    /**
     * @ORM\Column(name="details", type="text", nullable=true)
     */
    private $fileDetails;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private $views;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @ORM\OneToMany(targetEntity=PacientFileView::class, mappedBy="file")
     */
    private $pacientFileViews;

    /**
     * @ORM\Column(name="date_ddl_upload", type="date", nullable=true)
     */
    private $uploadDeadline;

    public function __construct()
    {
        $this->pacientFileViews = new ArrayCollection();
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

    public function getUploadedAt(): ?\DateTimeInterface
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(?\DateTimeInterface $uploadedAt): self
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }

    public function getUserResponsible(): ?User
    {
        return $this->userResponsible;
    }

    public function setUserResponsible(?User $userResponsible): self
    {
        $this->userResponsible = $userResponsible;

        return $this;
    }

    public function getFileType(): ?PacientFileType
    {
        return $this->fileType;
    }

    public function setFileType(?PacientFileType $fileType): self
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileNumber(): ?string
    {
        return $this->fileNumber;
    }

    public function setFileNumber(?string $fileNumber): self
    {
        $this->fileNumber = $fileNumber;

        return $this;
    }

    public function getFileDetails(): ?string
    {
        return $this->fileDetails;
    }

    public function setFileDetails(?string $fileDetails): self
    {
        $this->fileDetails = $fileDetails;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

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

    public function getFileDate(): ?\DateTimeInterface
    {
        return $this->fileDate;
    }

    public function setFileDate(?\DateTimeInterface $fileDate): self
    {
        $this->fileDate = $fileDate;

        return $this;
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

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): self
    {
        $this->views = $views;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getFileExtension(): ?string
    {
        return $this->fileExtension;
    }

    public function setFileExtension(?string $fileExtension): self
    {
        $this->fileExtension = $fileExtension;

        return $this;
    }
    
    public function getFormattedFilePath($pacientUuid): ?string {
        return sprintf('/%s/%s/%s', 'records', $pacientUuid, $this->filePath);
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

    /**
     * @return Collection<int, PacientFileView>
     */
    public function getPacientFileViews(): Collection
    {
        return $this->pacientFileViews;
    }

    public function addPacientFileView(PacientFileView $pacientFileView): self
    {
        if (!$this->pacientFileViews->contains($pacientFileView)) {
            $this->pacientFileViews[] = $pacientFileView;
            $pacientFileView->setFile($this);
        }

        return $this;
    }

    public function removePacientFileView(PacientFileView $pacientFileView): self
    {
        if ($this->pacientFileViews->removeElement($pacientFileView)) {
            // set the owning side to null (unless already changed)
            if ($pacientFileView->getFile() === $this) {
                $pacientFileView->setFile(null);
            }
        }

        return $this;
    }
    
    public function incrementViews() {
        $this->views++;
    }

    public function getUploadDeadline(): ?\DateTimeInterface
    {
        return $this->uploadDeadline;
    }

    public function setUploadDeadline(?\DateTimeInterface $uploadDeadline): self
    {
        $this->uploadDeadline = $uploadDeadline;

        return $this;
    }
}
