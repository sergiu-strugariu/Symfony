<?php

namespace App\Entity;

use App\Repository\SummaryTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SummaryTypeRepository::class)
 * @ORM\Table(name="borderouri__type")
 */
class SummaryType
{
    
    const SUMMARY_TYPE_MEDIC = 1;
    const SUMMARY_TYPE_PHYSICAL_THERAPY = 2;
    const SUMMARY_TYPE_PSYCHOTHERAPY = 3;
    const SUMMARY_TYPE_SOCIAL_WORKER = 4;
    const SUMMARY_TYPE_RECEPTION = 5;
    const SUMMARY_TYPE_ASSISTANCE_MEDICAL = 6;
    const SUMMARY_TYPE_ORDERLY = 7;
    const SUMMARY_TYPE_COOK = 8;
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

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
}
