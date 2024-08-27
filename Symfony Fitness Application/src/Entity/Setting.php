<?php

namespace App\Entity;

use App\Repository\SettingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
#[UniqueEntity(fields: ['settingName'], message: 'A setting with this name already exists.', errorPath: 'settingName')]
class Setting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $settingName = null;

    #[ORM\Column(length: 255)]
    private ?string $settingValue = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSettingName(): ?string
    {
        return $this->settingName;
    }

    public function setSettingName(string $settingName): static
    {
        $this->settingName = $settingName;

        return $this;
    }

    public function getSettingValue(): ?string
    {
        return $this->settingValue;
    }

    public function setSettingValue(string $settingValue): static
    {
        $this->settingValue = $settingValue;

        return $this;
    }
}
