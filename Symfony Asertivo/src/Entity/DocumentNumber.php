<?php

namespace App\Entity;

use App\Repository\DocumentNumberRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentNumberRepository::class)
 * @ORM\Table(name="files__pacients_numar")
 */
class DocumentNumber
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Document::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $document;

    /**
     * @ORM\ManyToOne(targetEntity=NursingHome::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $nursingHome;

    /**
     * @ORM\Column(type="integer", options={"default": 1})
     */
    private $documentNumber;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): self
    {
        $this->document = $document;

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

    public function getDocumentNumber(): ?int
    {
        return $this->documentNumber;
    }

    public function setDocumentNumber(int $documentNumber): self
    {
        $this->documentNumber = $documentNumber;

        return $this;
    }
    
    public function incrementDocumentNumber() {
        $this->documentNumber++;
    }
}
