<?php

declare(strict_types=1);

namespace App\Entity;

use App\Security\TwoFactor\BackUpCodesManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as FOSUser;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\TrustedDeviceInterface;

/**
 * User.
 *
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends FOSUser implements TwoFactorInterface, TrustedDeviceInterface, BackupCodeInterface
{
    const FLOW_STATUS_FINISHED = 'finished';
    const FLOW_STATUS_INCOMPLETE = 'incomplete';
    const FLOW_STATUS_CHANGE_PASSWORD = 'password_change';
    const DEFAULT_FLOW_STATUS = self::FLOW_STATUS_FINISHED;
    const ROLE_USER = 'ROLE_USER';
    const ROLE_READ_ONLY_USER = 'ROLE_READ_ONLY_USER';
    const ROLE_ANONYMOUS_USER = 'ROLE_ANONYMOUS_USER';
    const AVAILABLE_ROLES = [
        self::ROLE_USER => self::ROLE_USER,
        self::ROLE_READ_ONLY_USER => self::ROLE_READ_ONLY_USER,
        self::ROLE_ANONYMOUS_USER => self::ROLE_ANONYMOUS_USER,
    ];

    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $googleId;

    /**
     * @var Share[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Share", mappedBy="owner")
     */
    protected $shares;

    /**
     * @var Share[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Share", mappedBy="user")
     */
    protected $availableShares;

    /**
     * @var Avatar|null
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Avatar",
     *     mappedBy="user",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $avatar;

    /**
     * @var Directory
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Directory",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $inbox;

    /**
     * @var Directory
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Directory",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $lists;

    /**
     * @var Directory
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Directory",
     *     cascade={"persist", "remove"}
     * )
     */
    protected $trash;

    /**
     * @var string|null
     *
     * @ORM\Column(length=65525, nullable=true)
     */
    protected $encryptedPrivateKey;

    /**
     * @var string|null
     *
     * @ORM\Column(length=65525, nullable=true)
     */
    protected $publicKey;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    protected $domain;

    /**
     * @var string|null
     *
     * @ORM\Column(name="google_authenticator_secret", type="string", nullable=true)
     */
    protected $googleAuthenticatorSecret;

    /**
     * @var int|null
     *
     * @ORM\Column(name="trusted_version", type="integer", options={"default": 0})
     */
    protected $trustedVersion = 0;

    /**
     * @var Srp|null
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Srp",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     */
    protected $srp;

    /**
     * @var bool
     */
    private $credentialsNonExpired = true;

    /**
     * @var Collection|Fingerprint[]
     *
     * @ORM\OneToMany(targetEntity="Fingerprint", mappedBy="user", orphanRemoval=true, cascade={"persist"})
     */
    private $fingerprints = [];

    /**
     * @var string
     * @ORM\Column(type="string", options={"default": "finished"}, nullable=false)
     */
    private $flowStatus = self::DEFAULT_FLOW_STATUS;

    /**
     * @var array|null
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $backupCodes = [];

    /**
     * @var UserGroup[]|Collection
     *
     * @ORM\OneToMany(targetEntity="UserGroup", mappedBy="user", cascade={"persist"}, orphanRemoval=true)
     */
    private $userGroups;

    /**
     * User constructor.
     *
     * @param Srp|null $srp
     * @throws \Exception
     */
    public function __construct(Srp $srp = null)
    {
        parent::__construct();
        $this->id = Uuid::uuid4();
        $this->inbox = Directory::createInbox();
        $this->lists = Directory::createRootList();
        $this->trash = Directory::createTrash();
        $this->shares = new ArrayCollection();
        $this->userGroups = new ArrayCollection();
        $this->availableShares = new ArrayCollection();
        $this->fingerprints = new ArrayCollection();
        if (null !== $srp) {
            $this->srp = $srp;
        }
        $this->flowStatus = self::FLOW_STATUS_INCOMPLETE;
        BackUpCodesManager::generate($this);
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    /**
     * @param string $googleId
     */
    public function setGoogleId(string $googleId)
    {
        $this->googleId = $googleId;
    }

    /**
     * @return Avatar|null
     */
    public function getAvatar(): ?Avatar
    {
        return $this->avatar;
    }

    /**
     * @param Avatar|null $avatar
     */
    public function setAvatar(?Avatar $avatar): void
    {
        $this->avatar = $avatar;
        $avatar->setUser($this);
    }

    /**
     * @return Directory
     */
    public function getInbox(): Directory
    {
        return $this->inbox;
    }

    /**
     * @return Directory
     */
    public function getLists(): Directory
    {
        return $this->lists;
    }

    /**
     * @return Directory
     */
    public function getTrash(): Directory
    {
        return $this->trash;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @param null|string $domain
     */
    public function setDomain(?string $domain): void
    {
        $this->domain = $domain;
    }

    public function getUserDomain(): string
    {
        $emailDomain = explode('@', $this->getEmailCanonical());
        $emailDomain = end($emailDomain);

        return $this->getDomain() ?: $emailDomain;
    }

    /**
     * {@inheritdoc}
     */
    public function isGoogleAuthenticatorEnabled(): bool
    {
        return $this->googleAuthenticatorSecret ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getGoogleAuthenticatorSecret(): string
    {
        return (string) $this->googleAuthenticatorSecret;
    }

    public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): void
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->getEmail();
    }

    public function getTrustedVersion()
    {
        return $this->trustedVersion;
    }

    public function setTrustedVersion($trustedVersion): void
    {
        $this->trustedVersion = $trustedVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getTrustedTokenVersion(): int
    {
        return $this->trustedVersion;
    }

    /**
     * @return Share[]|Collection
     */
    public function getShares(): Collection
    {
        return $this->shares;
    }

    public function addShare(Share $share): void
    {
        if (!$this->shares->contains($share)) {
            $this->shares->add($share);
            $share->setOwner($this);
        }
    }

    public function removeShare(Share $share): void
    {
        $this->shares->removeElement($share);
    }

    /**
     * @return Share[]|Collection
     */
    public function getAvailableShares(): Collection
    {
        return $this->availableShares;
    }

    public function addAvailableShares(Share $availableShare): void
    {
        if ($this->availableShares->contains($availableShare)) {
            $this->availableShares->add($availableShare);
            $availableShare->setUser($this);
        }
    }

    public function getEncryptedPrivateKey(): ?string
    {
        return $this->encryptedPrivateKey;
    }

    public function setEncryptedPrivateKey(?string $encryptedPrivateKey): void
    {
        $this->encryptedPrivateKey = $encryptedPrivateKey;
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    public function setPublicKey(?string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    public function getSrp(): ?Srp
    {
        return $this->srp;
    }

    public function isCredentialsNonExpired(): bool
    {
        return $this->credentialsNonExpired;
    }

    public function setCredentialNonExpired(bool $flag)
    {
        $this->credentialsNonExpired = $flag;
    }

    public function removeFingerprint(Fingerprint $fingerprint): void
    {
        $this->fingerprints->removeElement($fingerprint);
    }

    public function addFingerprint(Fingerprint $fingerprint)
    {
        if (false === $this->fingerprints->contains($fingerprint)) {
            $this->fingerprints->add($fingerprint);
        }
    }

    public function getFingerprints(): Collection
    {
        return $this->fingerprints;
    }

    /**
     * @return string
     */
    public function getFlowStatus(): string
    {
        return $this->flowStatus;
    }

    /**
     * @param string $flowStatus
     */
    public function setFlowStatus(string $flowStatus): void
    {
        $this->flowStatus = $flowStatus;
    }

    /**
     * Check if it is a valid backup code.
     *
     * @param string $code
     *
     * @return bool
     */
    public function isBackupCode(string $code): bool
    {
        $encoder = BackUpCodesManager::initEncoder();
        $code = $encoder->encode($code);
        return in_array($code, $this->backupCodes);
    }

    /**
     * Invalidate a backup code.
     *
     * @param string $code
     */
    public function invalidateBackupCode(string $code): void
    {
        $encoder = BackUpCodesManager::initEncoder();
        $code = $encoder->encode($code);
        $key = array_search($code, $this->backupCodes);
        if ($key !== false){
            unset($this->backupCodes[$key]);
        }
    }

    /**
     * @param array|null $backupCodes
     */
    public function setBackupCodes(?array $backupCodes): void
    {
        $this->backupCodes = $backupCodes;
    }

    private function getBackupCodesCount(): int
    {
        return $this->backupCodes ? count($this->backupCodes) : 0;
    }

    public function hasBackupCodes(): bool
    {
        return (bool) $this->getBackupCodesCount();
    }

    /**
     * @return array
     */
    public function getBackupCodes(): array
    {
        $encoder = BackUpCodesManager::initEncoder();
        $codes = [];
        foreach ($this->backupCodes as $backupCode) {
            $codes[] = current($encoder->decode($backupCode));
        }

        return $codes;
    }

    /**
     * @return UserGroup[]|Collection
     */
    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }

    public function addUserGroup(UserGroup $userGroup): void
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups->add($userGroup);
            $userGroup->setUser($this);
        }
    }

    public function removeUserGroup(UserGroup $userGroup): void
    {
        $this->userGroups->removeElement($userGroup);
    }

    public function setUserGroups($userGroups): void
    {
        $this->userGroups = $userGroups;
    }
}
