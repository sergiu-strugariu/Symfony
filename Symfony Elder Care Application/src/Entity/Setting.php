<?php

namespace App\Entity;

use App\Repository\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SettingRepository::class)
 */
class Setting
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
    private ?string $settingName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $settingValue;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSettingName(): ?string
    {
        return $this->settingName;
    }

    public function setSettingName(string $settingName): self
    {
        $this->settingName = $settingName;

        return $this;
    }

    public function getSettingValue(): ?string
    {
        return $this->settingValue;
    }

    public function setSettingValue(string $settingValue): self
    {
        $this->settingValue = $settingValue;

        return $this;
    }
}
