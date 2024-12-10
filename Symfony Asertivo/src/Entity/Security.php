<?php

namespace App\Entity;

use App\Repository\SecurityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SecurityRepository::class)
 */
class Security
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $authImage;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthImage(): ?string
    {
        return $this->authImage;
    }

    public function setAuthImage(string $authImage): self
    {
        $this->authImage = $authImage;

        return $this;
    }
}
