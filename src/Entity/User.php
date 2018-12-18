<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as FOSUser;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\TrustedDeviceInterface;

/**
 * User.
 *
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends FOSUser implements TwoFactorInterface, TrustedDeviceInterface
{
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
     * @var bool
     *
     * @ORM\Column(name="guest", type="boolean", options={"default": false})
     */
    protected $guest = false;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->id = Uuid::uuid4();
        $this->inbox = Directory::createInbox();
        $this->lists = Directory::createRootList();
        $this->trash = Directory::createTrash();
        $this->shares = new ArrayCollection();
        $this->availableShares = new ArrayCollection();
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

    public function isGuest(): bool
    {
        return $this->guest;
    }

    public function setGuest(bool $guest): void
    {
        $this->guest = $guest;
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
}
