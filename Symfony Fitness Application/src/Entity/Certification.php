<?php

namespace App\Entity;

use App\Repository\CertificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CertificationRepository::class)]
class Certification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 100)]
    private ?string $imageName = null;

    /**
     * @var Collection<int, Education>
     */
    #[ORM\OneToMany(targetEntity: Education::class, mappedBy: 'certification', orphanRemoval: true)]
    private Collection $education;

    /**
     * @var Collection<int, CertificationTranslation>
     */
    #[ORM\OneToMany(targetEntity: CertificationTranslation::class, mappedBy: 'certification', orphanRemoval: true)]
    private Collection $certificationTranslations;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\ManyToOne(inversedBy: 'certifications')]
    private ?CertificationCategory $certificateCategory = null;

    private $defaultLocale = 'ro';
    
    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->education = new ArrayCollection();
        $this->certificationTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * @return Collection<int, Education>
     */
    public function getEducation(): Collection
    {
        return $this->education;
    }

    public function addEducation(Education $education): static
    {
        if (!$this->education->contains($education)) {
            $this->education->add($education);
            $education->setCertification($this);
        }

        return $this;
    }

    public function removeEducation(Education $education): static
    {
        if ($this->education->removeElement($education)) {
            // set the owning side to null (unless already changed)
            if ($education->getCertification() === $this) {
                $education->setCertification(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CertificationTranslation>
     */
    public function getCertificationTranslations(): Collection
    {
        return $this->certificationTranslations;
    }

    public function addCertificationTranslation(CertificationTranslation $certificationTranslation): static
    {
        if (!$this->certificationTranslations->contains($certificationTranslation)) {
            $this->certificationTranslations->add($certificationTranslation);
            $certificationTranslation->setCertification($this);
        }

        return $this;
    }

    public function removeCertificationTranslation(CertificationTranslation $certificationTranslation): static
    {
        if ($this->certificationTranslations->removeElement($certificationTranslation)) {
            // set the owning side to null (unless already changed)
            if ($certificationTranslation->getCertification() === $this) {
                $certificationTranslation->setCertification(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getTranslation($locale)
    {
        $translations = $this->getCertificationTranslations()->filter(function ($certificationTranslation) use ($locale) {
            return $certificationTranslation->getLanguage()->getLocale() === $locale;
        });
        
        if ($translations->isEmpty()) {
            return $this->getCertificationTranslations()->filter(function ($certificationTranslation) {
                return $certificationTranslation->getLanguage()->getLocale() === $this->defaultLocale;
            })->first();
        }
        
        return $translations->first();
    }

    public function getCertificateCategory(): ?CertificationCategory
    {
        return $this->certificateCategory;
    }

    public function setCertificateCategory(?CertificationCategory $certificateCategory): static
    {
        $this->certificateCategory = $certificateCategory;

        return $this;
    }
}
