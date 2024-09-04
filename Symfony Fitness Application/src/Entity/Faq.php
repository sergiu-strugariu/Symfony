<?php

namespace App\Entity;

use App\Repository\FaqRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FaqRepository::class)]
class Faq
{
    private $defaultLocale = 'ro';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\OneToMany(targetEntity: FaqTranslations::class, mappedBy: 'faq', orphanRemoval: true)]
    private Collection $faqTranslations;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->faqTranslations = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getFAQTranslations(): Collection
    {
        return $this->faqTranslations;
    }

    public function addFAQTranslation(FaqTranslations $FAQTranslations): static
    {
        if (!$this->faqTranslations->contains($FAQTranslations)) {
            $this->faqTranslations->add($FAQTranslations);
            $FAQTranslations->setFAQ($this);
        }

        return $this;
    }

    public function removeFAQTranslation(FaqTranslations $FAQTranslations): static
    {
        if ($this->faqTranslations->removeElement($FAQTranslations)) {
            // set the owning side to null (unless already changed)
            if ($FAQTranslations->getFAQ() === $this) {
                $FAQTranslations->setFAQ(null);
            }
        }

        return $this;
    }

    public function getTranslation($locale)
    {
        $translations = $this->getFAQTranslations()->filter(function ($FAQTranslations) use ($locale) {
            return $FAQTranslations->getLanguage()->getLocale() === $locale;
        });

        if ($translations->isEmpty()) {
            return $this->getFAQTranslations()->filter(function ($FAQTranslations) {
                return $FAQTranslations->getLanguage()->getLocale() === $this->defaultLocale;
            })->first();
        }

        return $translations->first();
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
}

