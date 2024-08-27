<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQUE_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    
    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_CLIENT = 'ROLE_CLIENT';

    const ROLE_ADMIN = 'ROLE_ADMIN';
    const INVOICE_TYPE_INDIVIDUAL = 'PF';
    const INVOICE_TYPE_COMPANY = 'PJ';
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 40, unique: true)]
    private ?string $uuid = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;
    
    #[ORM\Column]
    private ?bool $enabled = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $passwordRequestedAt = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $confirmationToken = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $cnp = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $companyName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $cui = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $registrationNumber = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $companyAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bankName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bankAccount = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 20, options: ['default' => self::INVOICE_TYPE_INDIVIDUAL])]
    private ?string $invoiceType = self::INVOICE_TYPE_INDIVIDUAL;

    #[ORM\Column]
    private ?bool $accordGDPR = null;

    #[ORM\Column]
    private ?bool $newsletter = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLoginAt = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * @var Collection<int, EducationRegistration>
     */
    #[ORM\OneToMany(targetEntity: EducationRegistration::class, mappedBy: 'user')]
    private Collection $educationRegistrations;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $tempEmail = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->educationRegistrations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }


    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->getFirstName() . " " . $this->getLastName();
    }

    public function isAccordGDPR(): ?bool
    {
        return $this->accordGDPR;
    }

    public function setAccordGDPR(bool $accordGDPR): static
    {
        $this->accordGDPR = $accordGDPR;

        return $this;
    }

    public function isNewsletter(): ?bool
    {
        return $this->newsletter;
    }

    public function setNewsletter(bool $newsletter): static
    {
        $this->newsletter = $newsletter;

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

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?\DateTimeInterface $passwordRequestedAt): static
    {
        $this->passwordRequestedAt = $passwordRequestedAt;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): static
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

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

    public function getLastLoginAt(): ?\DateTimeInterface
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeInterface $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;

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

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }
    
    public function getRole() {
        return empty($this->roles) ? self::ROLE_DEFAULT : reset($this->roles);
    }

    public function setRole($role) {
        $this->roles = [];
        $this->addRole($role);

        return $this;
    }

    public function addRole($role) {
        $role = strtoupper($role);
        if ($role === self::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->getRoles(), true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole($role) {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function hasRole($role) {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function getCnp(): ?string
    {
        return $this->cnp;
    }

    public function setCnp(?string $cnp): static
    {
        $this->cnp = $cnp;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): static
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getCui(): ?string
    {
        return $this->cui;
    }

    public function setCui(?string $cui): static
    {
        $this->cui = $cui;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): static
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

    public function getCompanyAddress(): ?string
    {
        return $this->companyAddress;
    }

    public function setCompanyAddress(?string $companyAddress): static
    {
        $this->companyAddress = $companyAddress;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(?string $bankName): static
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getBankAccount(): ?string
    {
        return $this->bankAccount;
    }

    public function setBankAccount(?string $bankAccount): static
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getInvoiceType(): ?string
    {
        return $this->invoiceType;
    }

    public function setInvoiceType(string $invoiceType): static
    {
        $this->invoiceType = $invoiceType;

        return $this;
    }
    
    public static function getInvoiceTypes() {
        return [
           'Persoana fizica' => self::INVOICE_TYPE_INDIVIDUAL,
           'Persoana juridica' => self::INVOICE_TYPE_COMPANY
        ];
    }

    /**
     * @return Collection<int, EducationRegistration>
     */
    public function getEducationRegistrations(): Collection
    {
        return $this->educationRegistrations;
    }

    public function addEducationRegistration(EducationRegistration $educationRegistration): static
    {
        if (!$this->educationRegistrations->contains($educationRegistration)) {
            $this->educationRegistrations->add($educationRegistration);
            $educationRegistration->setUser($this);
        }

        return $this;
    }

    public function removeEducationRegistration(EducationRegistration $educationRegistration): static
    {
        if ($this->educationRegistrations->removeElement($educationRegistration)) {
            // set the owning side to null (unless already changed)
            if ($educationRegistration->getUser() === $this) {
                $educationRegistration->setUser(null);
            }
        }

        return $this;
    }

    public function getTempEmail(): ?string
    {
        return $this->tempEmail;
    }

    public function setTempEmail(?string $tempEmail): static
    {
        $this->tempEmail = $tempEmail;

        return $this;
    }
}
