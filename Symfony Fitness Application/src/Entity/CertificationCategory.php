<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use App\Repository\CertificationCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CertificationCategoryRepository::class)]
class CertificationCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $classes = null;
    
    private $defaultLocale = 'ro';

    /**
     * @var Collection<int, Certification>
     */
    #[ORM\OneToMany(targetEntity: Certification::class, mappedBy: 'certificateCategory')]
    private Collection $certifications;

    /**
     * @var Collection<int, CertificationCategoryTranslation>
     */
    #[ORM\OneToMany(targetEntity: CertificationCategoryTranslation::class, mappedBy: 'certificationCategory', orphanRemoval: true)]
    private Collection $certificationCategoryTranslations;

    public function __construct()
    {
        $this->certifications = new ArrayCollection();
        $this->certificationCategoryTranslations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Certification>
     */
    public function getCertifications(): Collection
    {
        return $this->certifications;
    }

    public function addCertification(Certification $certification): static
    {
        if (!$this->certifications->contains($certification)) {
            $this->certifications->add($certification);
            $certification->setCertificateCategory($this);
        }

        return $this;
    }

    public function removeCertification(Certification $certification): static
    {
        if ($this->certifications->removeElement($certification)) {
            // set the owning side to null (unless already changed)
            if ($certification->getCertificateCategory() === $this) {
                $certification->setCertificateCategory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CertificationCategoryTranslation>
     */
    public function getCertificationCategoryTranslations(): Collection
    {
        return $this->certificationCategoryTranslations;
    }

    public function addCertificationCategoryTranslation(CertificationCategoryTranslation $certificationCategoryTranslation): static
    {
        if (!$this->certificationCategoryTranslations->contains($certificationCategoryTranslation)) {
            $this->certificationCategoryTranslations->add($certificationCategoryTranslation);
            $certificationCategoryTranslation->setCertificationCategory($this);
        }

        return $this;
    }

    public function removeCertificationCategoryTranslation(CertificationCategoryTranslation $certificationCategoryTranslation): static
    {
        if ($this->certificationCategoryTranslations->removeElement($certificationCategoryTranslation)) {
            // set the owning side to null (unless already changed)
            if ($certificationCategoryTranslation->getCertificationCategory() === $this) {
                $certificationCategoryTranslation->setCertificationCategory(null);
            }
        }

        return $this;
    }

    public function getTranslation($locale)
    {
        $translations = $this->getCertificationCategoryTranslations()->filter(function ($certificationCategoryTranslation) use ($locale) {
            return $certificationCategoryTranslation->getLanguage()->getLocale() === $locale;
        });
        
        if ($translations->isEmpty()) {
            return $this->getCertificationCategoryTranslations()->filter(function ($certificationCategoryTranslation) {
                return $certificationCategoryTranslation->getLanguage()->getLocale() === $this->defaultLocale;
            })->first();
        }
        
        return $translations->first();
    }

    public function getClasses(): ?string
    {
        return $this->classes;
    }

    public function setClasses(?string $classes): static
    {
        $this->classes = $classes;

        return $this;
    }
}
