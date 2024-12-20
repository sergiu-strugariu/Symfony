<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    const ROLE_DEFAULT = 'ROLE_USER';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const CLOUDFLARE_R2_FILE_PATH = 'https://pub-93fb0211d8c644ada263105e41586cec.r2.dev';
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length="100", unique=true)
     */
    private $uid;
    
    /**
     * @ORM\ManyToOne(targetEntity=NursingHome::class, inversedBy="users")
     * @ORM\JoinColumn(name="id_camin", nullable=true)
     */
    private $nursingHome;

    /**
     * @ORM\ManyToOne(targetEntity=MembershipPackage::class, inversedBy="users")
     * @ORM\JoinColumn(name="membership_package_id", nullable=true)
     */
    private ?MembershipPackage $membershipPackage;

    /**
     * @ORM\ManyToOne(targetEntity=City::class)
     * @ORM\JoinColumn(name="city_id", nullable=true)
     */
    private $city;

    /**
     * @ORM\ManyToOne(targetEntity=County::class)
     * @ORM\JoinColumn(name="county_id", nullable=true)
     */
    private $county;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;
    
    /**
     * @ORM\Column(name="redirect_login", type="string", length=150, nullable=true)
     */
    private $loginRedirect;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length="15", nullable=true)
     */
    private $cnp;

    /**
     * @ORM\Column(name="nr_telefon", type="string", length=15, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(name="serie_ci", type="string", length=10, nullable=true)
     */
    private $idCardSeries;

    /**
     * @ORM\Column(name="numar_ci", type="integer", nullable=true)
     */
    private $idCardNumber;

    /**
     * @ORM\Column(name="data_nasterii", type="date", nullable=true)
     */
    private $dateOfBirth;

    /**
     * @ORM\Column(name="domiciliu", type="text", nullable=true)
     */
    private $homeAddress;

    /**
     * @ORM\Column(name="cetatenie", type="string", length=50, nullable=true)
     */
    private $citizenship;

    /**
     * @ORM\Column(name="photo_path", type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $paymentToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $paymentTokenExpirationDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $membershipCancel;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $membershipExpiresAt;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deletedAt;
    
    /**
     * @ORM\OneToMany(targetEntity=UserRelation::class, mappedBy="pacientRelation")
     */
    private $pacients;

    /**
     * @ORM\OneToMany(targetEntity=PacientAdmission::class, mappedBy="admittedBy")
     */
    private $admissions;

    /**
     * @ORM\OneToMany(targetEntity=PacientDischarge::class, mappedBy="dischargedBy")
     */
    private $pacientDischargesBy;

    /**
     * @ORM\Column(name="functie", type="string", length=150, nullable=true)
     */
    private $jobName;

    /**
     * @ORM\ManyToOne(targetEntity=NursingHomeRoom::class)
     */
    private $nursingHomeRoom;

    /**
     * @ORM\OneToMany(targetEntity=NursingHome::class, mappedBy="user")
     */
    private $nursingHomes;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $confirmationToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $passwordRequestedAt;

    /**
     * @ORM\OneToMany(targetEntity=UserBillingData::class, mappedBy="user")
     */
    private $userBillingData;

    /**
     * @ORM\OneToMany(targetEntity=Payment::class, mappedBy="user")
     */
    private $payments;

    public function __construct()
    {
        $this->pacients = new ArrayCollection();
        $this->admissions = new ArrayCollection();
        $this->pacientDischargesBy = new ArrayCollection();
        $this->nursingHomes = new ArrayCollection();
        $this->userBillingData = new ArrayCollection();
        $this->payments = new ArrayCollection();
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
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
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
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

        if (!in_array($role, $this->roles, true)) {
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
    
    public function getFormattedRole() {
        $role = $this->getRole();
        
        return self::getRoleAsString($role);
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
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

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getLoginRedirect(): ?string
    {
        return $this->loginRedirect;
    }

    public function setLoginRedirect(?string $loginRedirect): self
    {
        $this->loginRedirect = $loginRedirect;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->firstName . " " . $this->lastName;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }
    
    public function getName() {
        if (empty($this->firstName) && empty($this->lastName)) {
            return 'Fara nume';
        }
        
        return $this->lastName . ' ' . $this->firstName;
    }

    public function getCnp(): ?int
    {
        return $this->cnp;
    }

    public function setCnp(?int $cnp): self
    {
        $this->cnp = $cnp;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getIdCardSeries(): ?string
    {
        return $this->idCardSeries;
    }

    public function setIdCardSeries(?string $idCardSeries): self
    {
        $this->idCardSeries = $idCardSeries;

        return $this;
    }

    public function getIdCardNumber(): ?int
    {
        return $this->idCardNumber;
    }

    public function setIdCardNumber(?int $idCardNumber): self
    {
        $this->idCardNumber = $idCardNumber;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getHomeAddress(): ?string
    {
        return $this->homeAddress;
    }

    public function setHomeAddress(?string $homeAddress): self
    {
        $this->homeAddress = $homeAddress;

        return $this;
    }

    public function getCitizenship(): ?string
    {
        return $this->citizenship;
    }

    public function setCitizenship(?string $citizenship): self
    {
        $this->citizenship = $citizenship;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
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
    
    public function getFormattedCreatedAt($format): ?string
    {
        return $this->createdAt->format($format);
    }
    
    /**
     * @return Collection<int, UserRelation>
     */
    public function getPacients(): Collection
    {
        return $this->pacients;
    }

    public function addPacient(UserRelation $pacient): self
    {
        if (!$this->pacients->contains($pacient)) {
            $this->pacients[] = $pacient;
            $pacient->setPacient($this);
        }

        return $this;
    }

    public function removePacient(UserRelation $pacient): self
    {
        if ($this->pacients->removeElement($pacient)) {
            // set the owning side to null (unless already changed)
            if ($pacient->getPacient() === $this) {
                $pacient->setPacient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacientAdmission>
     */
    public function getAdmissions(): Collection
    {
        return $this->admissions;
    }

    public function addAdmission(PacientAdmission $admission): self
    {
        if (!$this->admissions->contains($admission)) {
            $this->admissions[] = $admission;
            $admission->setAdmittedBy($this);
        }

        return $this;
    }

    public function removeAdmission(PacientAdmission $admission): self
    {
        if ($this->admissions->removeElement($admission)) {
            // set the owning side to null (unless already changed)
            if ($admission->getAdmittedBy() === $this) {
                $admission->setAdmittedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PacientDischarge>
     */
    public function getPacientDischargesBy(): Collection
    {
        return $this->pacientDischargesBy;
    }

    public function addPacientDischargesBy(PacientDischarge $pacientDischargesBy): self
    {
        if (!$this->pacientDischargesBy->contains($pacientDischargesBy)) {
            $this->pacientDischargesBy[] = $pacientDischargesBy;
            $pacientDischargesBy->setDischargedBy($this);
        }

        return $this;
    }

    public function removePacientDischargesBy(PacientDischarge $pacientDischargesBy): self
    {
        if ($this->pacientDischargesBy->removeElement($pacientDischargesBy)) {
            // set the owning side to null (unless already changed)
            if ($pacientDischargesBy->getDischargedBy() === $this) {
                $pacientDischargesBy->setDischargedBy(null);
            }
        }

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCounty(): ?County
    {
        return $this->county;
    }

    public function setCounty(?County $county): self
    {
        $this->county = $county;

        return $this;
    }

    public function getJobName(): ?string
    {
        return $this->jobName;
    }

    public function setJobName(?string $jobName): self
    {
        $this->jobName = $jobName;

        return $this;
    }

    public function getNursingHomeRoom(): ?NursingHomeRoom
    {
        return $this->nursingHomeRoom;
    }

    public function setNursingHomeRoom(?NursingHomeRoom $nursingHomeRoom): self
    {
        $this->nursingHomeRoom = $nursingHomeRoom;

        return $this;
    }
    
    public function getPhotoPath() 
    {
        $package = new UrlPackage(self::CLOUDFLARE_R2_FILE_PATH, new EmptyVersionStrategy());
        return (empty($this->photo)) ? $package->getUrl('/assets/media/avatars/blank.png') : $package->getUrl(sprintf('/%s/%s', 'uploads', $this->photo));
    }

    public function getTreatmentPlanAuditedBy(): ?self
    {
        return $this->treatmentPlanAuditedBy;
    }

    public function setTreatmentPlanAuditedBy(?self $treatmentPlanAuditedBy): self
    {
        $this->treatmentPlanAuditedBy = $treatmentPlanAuditedBy;

        return $this;
    }

    public function getTreatmentPlanAuditDate(): ?\DateTimeInterface
    {
        return $this->treatmentPlanAuditDate;
    }

    public function setTreatmentPlanAuditDate(?\DateTimeInterface $treatmentPlanAuditDate): self
    {
        $this->treatmentPlanAuditDate = $treatmentPlanAuditDate;

        return $this;
    }

    public function getTreatmentPlanAuditObservations(): ?string
    {
        return $this->treatmentPlanAuditObservations;
    }

    public function setTreatmentPlanAuditObservations(?string $treatmentPlanAuditObservations): self
    {
        $this->treatmentPlanAuditObservations = $treatmentPlanAuditObservations;

        return $this;
    }

    public function isTreatmentPlanValid(): ?bool
    {
        return $this->treatmentPlanValid;
    }

    public function setTreatmentPlanValid(?bool $treatmentPlanValid): self
    {
        $this->treatmentPlanValid = $treatmentPlanValid;

        return $this;
    }
    
    public static function getRoleAsString($role) {
        $roleAsString = '';
        
        switch ($role) {
            case 'ROLE_ADMIN':
                $roleAsString = 'admin';
                break;
             case 'ROLE_MEDIC':
                $roleAsString = 'medic';
                break;
             case 'ROLE_RELATION':
                $roleAsString = 'apartinator';
                break;
            case 'ROLE_OWNER':
                $roleAsString = 'owner';
                break;
            case 'ROLE_MANAGEMENT':
                $roleAsString = 'manager';
                break;
            case 'ROLE_ASSISTANCE_MEDICAL':
                $roleAsString = 'asistent medical';
                break;
             case 'ROLE_ASSISTANCE_MEDICAL_PHARMACY':
                $roleAsString = 'asistent medical farmacie';
                break;
            case 'ROLE_PHYSICAL_THERAPY':
                $roleAsString = 'kinetoterapeut';
                break;
            case 'ROLE_ORDERLY':
                $roleAsString = 'infirmier';
                break;
            case 'ROLE_SOCIAL_WORKER':
                $roleAsString = 'asistent social';
                break;
            case 'ROLE_COOK':
                $roleAsString = 'bucatar';
                break;
            case 'ROLE_CLEANING':
                $roleAsString = 'mentenanta';
                break;
            case 'ROLE_RECEPTION':
                $roleAsString = 'receptie';
                break;
             case 'ROLE_PSYCHOTHERAPY':
                $roleAsString = 'psihoterapeut';
                break;
            default:
                break;
        }
        
        return $roleAsString;
    }

    /**
     * @return Collection<int, NursingHome>
     */
    public function getNursingHomes(): Collection
    {
        return $this->nursingHomes;
    }

    public function addNursingHome(NursingHome $nursingHome): self
    {
        if (!$this->nursingHomes->contains($nursingHome)) {
            $this->nursingHomes[] = $nursingHome;
            $nursingHome->setUser($this);
        }

        return $this;
    }

    public function removeNursingHome(NursingHome $nursingHome): self
    {
        if ($this->nursingHomes->removeElement($nursingHome)) {
            // set the owning side to null (unless already changed)
            if ($nursingHome->getUser() === $this) {
                $nursingHome->setUser(null);
            }
        }

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function getPasswordRequestedAt(): ?\DateTimeInterface
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt(?\DateTimeInterface $passwordRequestedAt): self
    {
        $this->passwordRequestedAt = $passwordRequestedAt;

        return $this;
    }

    /**
     * @return Collection<int, UserBillingData>
     */
    public function getUserBillingData(): Collection
    {
        return $this->userBillingData;
    }

    public function addUserBillingData(UserBillingData $userBillingData): self
    {
        if (!$this->userBillingData->contains($userBillingData)) {
            $this->userBillingData[] = $userBillingData;
            $userBillingData->setUser($this);
        }

        return $this;
    }

    public function removeUserBillingData(UserBillingData $userBillingData): self
    {
        if ($this->userBillingData->removeElement($userBillingData)) {
            // set the owning side to null (unless already changed)
            if ($userBillingData->getUser() === $this) {
                $userBillingData->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setUser($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        if ($this->payments->removeElement($payment)) {
            // set the owning side to null (unless already changed)
            if ($payment->getUser() === $this) {
                $payment->setUser(null);
            }
        }

        return $this;
    }

    public function getMembershipPackage(): ?MembershipPackage
    {
        return $this->membershipPackage;
    }

    public function setMembershipPackage(?MembershipPackage $membershipPackage): self
    {
        $this->membershipPackage = $membershipPackage;

        return $this;
    }

    public function getPaymentToken(): ?string
    {
        return $this->paymentToken;
    }

    public function setPaymentToken(?string $paymentToken): self
    {
        $this->paymentToken = $paymentToken;

        return $this;
    }

    public function getPaymentTokenExpirationDate(): ?\DateTimeInterface
    {
        return $this->paymentTokenExpirationDate;
    }

    public function setPaymentTokenExpirationDate(?\DateTimeInterface $paymentTokenExpirationDate): self
    {
        $this->paymentTokenExpirationDate = $paymentTokenExpirationDate;

        return $this;
    }

    public function isMembershipCancel(): ?bool
    {
        return $this->membershipCancel;
    }

    public function setMembershipCancel(bool $membershipCancel): self
    {
        $this->membershipCancel = $membershipCancel;

        return $this;
    }

    public function getMembershipExpiresAt(): ?\DateTimeInterface
    {
        return $this->membershipExpiresAt;
    }

    public function setMembershipExpiresAt(?\DateTimeInterface $membershipExpiresAt): self
    {
        $this->membershipExpiresAt = $membershipExpiresAt;

        return $this;
    }
}
