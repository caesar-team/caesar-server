<?php

declare(strict_types=1);

namespace App\Entity;

use App\Security\TwoFactor\BackUpCodesManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as FOSUser;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\TrustedDeviceInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User.
 *
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="Wrong email")
 */
class User extends FOSUser implements TwoFactorInterface, TrustedDeviceInterface, BackupCodeInterface
{
    public const FLOW_STATUS_FINISHED = 'finished';
    public const FLOW_STATUS_INCOMPLETE = 'incomplete';
    public const FLOW_STATUS_CHANGE_PASSWORD = 'password_change';
    public const DEFAULT_FLOW_STATUS = self::FLOW_STATUS_FINISHED;
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_ADMIN = 'ROLE_ADMIN';
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const ROLE_READ_ONLY_USER = 'ROLE_READ_ONLY_USER';
    public const ROLE_ANONYMOUS_USER = 'ROLE_ANONYMOUS_USER';
    public const ROLE_SYSTEM_USER = 'ROLE_SYSTEM_USER';
    public const AVAILABLE_ROLES = [
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
     * @var int
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
    private $fingerprints;

    /**
     * @var string
     * @ORM\Column(type="string", options={"default": "finished"}, nullable=false)
     */
    private $flowStatus;

    /**
     * @var array
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $backupCodes = [];

    /**
     * @var UserTeam[]|Collection
     *
     * @ORM\OneToMany(targetEntity="UserTeam", mappedBy="user", cascade={"persist"}, orphanRemoval=true)
     */
    private $userTeams;

    /**
     * @var Collection|Item[]
     * @ORM\OneToMany(targetEntity="App\Entity\Item", mappedBy="owner")
     */
    private $ownedItems;

    /**
     * @var Directory
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Directory", inversedBy="userInbox", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $inbox;

    /**
     * @var Directory
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Directory", inversedBy="userLists", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $lists;

    /**
     * @var Directory
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Directory", inversedBy="userTrash", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $trash;

    /**
     * @var Collection|Directory[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Directory", mappedBy="user")
     * @ORM\OrderBy({"sort": "ASC"})
     */
    protected $directories;

    /**
     * User constructor.
     *
     * @throws \Exception
     */
    public function __construct(Srp $srp = null)
    {
        parent::__construct();
        $this->id = Uuid::uuid4();
        $this->inbox = Directory::createInbox();
        $this->lists = Directory::createRootList();
        $this->lists->addChildList(Directory::createDefaultList());
        $this->trash = Directory::createTrash();

        $this->inbox->setUser($this);
        $this->trash->setUser($this);
        $this->lists->setUser($this);
        $this->userTeams = new ArrayCollection();
        $this->fingerprints = new ArrayCollection();
        $this->ownedItems = new ArrayCollection();
        if (null !== $srp) {
            $this->srp = $srp;
        }
        $this->flowStatus = self::FLOW_STATUS_INCOMPLETE;
        BackUpCodesManager::generate($this);
        $this->directories = new ArrayCollection();
    }

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

    public function setGoogleId(string $googleId)
    {
        $this->googleId = $googleId;
    }

    public function getAvatar(): ?Avatar
    {
        return $this->avatar;
    }

    public function getAvatarLink(): ?string
    {
        return null !== $this->avatar ? $this->avatar->getLink() : null;
    }

    public function setAvatar(?Avatar $avatar): void
    {
        $this->avatar = $avatar;
        $avatar->setUser($this);
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

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

    public function getTrustedVersion(): ?int
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

    public function hasKeys(): bool
    {
        return null !== $this->getEncryptedPrivateKey() && null !== $this->getPublicKey();
    }

    public function getSrp(): ?Srp
    {
        return $this->srp;
    }

    public function setSrp(Srp $srp): void
    {
        $this->srp = $srp;
    }

    public function isCredentialsNonExpired(): bool
    {
        return $this->credentialsNonExpired;
    }

    public function setCredentialNonExpired(bool $flag): void
    {
        $this->credentialsNonExpired = $flag;
    }

    public function removeFingerprint(Fingerprint $fingerprint): void
    {
        $this->fingerprints->removeElement($fingerprint);
    }

    public function addFingerprint(Fingerprint $fingerprint): void
    {
        if (false === $this->fingerprints->contains($fingerprint)) {
            $this->fingerprints->add($fingerprint);
            $fingerprint->setUser($this);
        }
    }

    /**
     * @return Fingerprint[]
     */
    public function getFingerprints(): array
    {
        return $this->fingerprints->toArray();
    }

    public function invalidateFingerprints(): void
    {
        foreach ($this->getFingerprints() as $fingerprint) {
            if ($fingerprint->isValidExpired()) {
                continue;
            }

            $this->removeFingerprint($fingerprint);
        }
    }

    public function getFlowStatus(): string
    {
        return $this->flowStatus;
    }

    public function setFlowStatus(string $flowStatus): void
    {
        $this->flowStatus = $flowStatus;
    }

    public function isAccepted(): bool
    {
        return self::FLOW_STATUS_FINISHED === $this->flowStatus;
    }

    /**
     * Check if it is a valid backup code.
     */
    public function isBackupCode(string $code): bool
    {
        $encoder = BackUpCodesManager::initEncoder();
        $code = $encoder->encode($code);

        return in_array($code, $this->backupCodes);
    }

    /**
     * Invalidate a backup code.
     */
    public function invalidateBackupCode(string $code): void
    {
        $encoder = BackUpCodesManager::initEncoder();
        $code = $encoder->encode($code);
        $key = array_search($code, $this->backupCodes);
        if (false !== $key) {
            unset($this->backupCodes[$key]);
        }
    }

    public function setBackupCodes(array $backupCodes): void
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
     * @return UserTeam[]
     */
    public function getUserTeams(): array
    {
        return $this->userTeams->toArray();
    }

    public function getUserTeamByTeam(Team $team): ?UserTeam
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('team', $team));

        /**
         * @psalm-suppress UndefinedInterfaceMethod
         * @phpstan-ignore-next-line
         */
        $userTeam = $this->userTeams->matching($criteria)->first();

        return $userTeam instanceof UserTeam ? $userTeam : null;
    }

    public function addUserTeam(UserTeam $userTeam): void
    {
        if (!$this->userTeams->contains($userTeam)) {
            $this->userTeams->add($userTeam);
            $userTeam->setUser($this);
        }
    }

    public function removeUserTeam(UserTeam $userTeam): void
    {
        $this->userTeams->removeElement($userTeam);
    }

    public function setUserTeams($userTeams): void
    {
        $this->userTeams = $userTeams;
    }

    public function isFullUser(): bool
    {
        return !$this->hasRole(self::ROLE_ANONYMOUS_USER) && !$this->hasRole(self::ROLE_READ_ONLY_USER);
    }

    public function isAnonymous(): bool
    {
        return $this->hasRole(self::ROLE_ANONYMOUS_USER);
    }

    /**
     * @return Item[]
     */
    public function getOwnedItems(): array
    {
        return $this->ownedItems->toArray();
    }

    /**
     * @return Item[]
     */
    public function getPersonalItems(): array
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('team'));
        $criteria->orderBy(['sort' => Criteria::ASC]);

        /**
         * @psalm-suppress UndefinedInterfaceMethod
         * @phpstan-ignore-next-line
         */
        return $this->ownedItems->matching($criteria)->toArray();
    }

    public function addOwnedItem(Item $item): void
    {
        if (!$this->ownedItems->contains($item)) {
            $this->ownedItems->add($item);
            $item->setOwner($this);
        }
    }

    public function removeOwnedItem(Item $item): void
    {
        $this->ownedItems->removeElement($item);
    }

    /**
     * @return string[]
     */
    public function getTeamsIds(): array
    {
        return array_map(function (UserTeam $userTeam) {
            return $userTeam->getTeam()->getId()->toString();
        }, $this->userTeams->toArray());
    }

    public function getInbox(): Directory
    {
        $this->inbox->setRole(Directory::LIST_INBOX);

        return $this->inbox;
    }

    public function getLists(): Directory
    {
        $this->lists->setRole(Directory::LIST_ROOT_LIST);

        return $this->lists;
    }

    public function getTrash(): Directory
    {
        $this->trash->setRole(Directory::LIST_TRASH);

        return $this->trash;
    }

    public function hasOneOfRoles(array $roles): bool
    {
        return (bool) array_filter($roles, function ($role) {
            return $this->hasRole($role);
        });
    }

    /**
     * @return Team[]
     */
    public function getTeams(): array
    {
        return array_map(function (UserTeam $userTeam) {
            return $userTeam->getTeam();
        }, $this->userTeams->toArray());
    }

    public function equals(?User $user): bool
    {
        return null !== $user && $this->getId()->toString() === $user->getId()->toString();
    }

    public function getUserPersonalLists(): array
    {
        $lists = $this->getLists()->getChildLists()->toArray();
        $lists[] = $this->getInbox();
        $lists[] = $this->getTrash();

        return $lists;
    }

    public function isOwnerByDirectory(?Directory $directory): bool
    {
        if (null === $directory) {
            return false;
        }

        return $this->getInbox()->equals($directory)
            || $this->getTrash()->equals($directory)
            || $this->getLists()->equals($directory)
            || $this->getLists()->hasChildListByDirectory($directory)
        ;
    }

    public function getDefaultDirectory(): ?Directory
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->eq('label', Directory::LIST_DEFAULT));

        /**
         * @psalm-suppress UndefinedInterfaceMethod
         * @phpstan-ignore-next-line
         */
        $directory = $this->getLists()->getChildLists()->matching($criteria)->first();

        return $directory instanceof Directory ? $directory : null;
    }

    public function isIncomplete(): bool
    {
        return self::FLOW_STATUS_INCOMPLETE === $this->flowStatus;
    }

    /**
     * @return Directory[]
     */
    public function getDirectories(): array
    {
        return $this->directories->toArray();
    }

    /**
     * @param Directory[]|Collection $directories
     */
    public function setDirectories(Collection $directories): void
    {
        $this->directories = $directories;
    }

    public function addDirectory(Directory $directory): void
    {
        if (!$this->directories->contains($directory)) {
            $this->directories->add($directory);
            $directory->setUser($this);
        }
    }
}
