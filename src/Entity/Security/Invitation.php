<?php

declare(strict_types=1);

namespace App\Entity\Security;

use App\Security\AuthorizationManager\InvitationEncoder;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class Invitation.
 *
 * @ORM\Entity(repositoryClass="App\Repository\InvitationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Invitation
{
    use TimestampableEntity;

    public const DEFAULT_SHELF_LIFE = '+1 day';
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(unique=true, type="text", nullable=false)
     */
    protected $hash;

    /**
     * @var string
     * @ORM\Column(type="string", length=10, nullable=false, options={"default": "+1 day"})
     */
    protected $shelfLife = self::DEFAULT_SHELF_LIFE;

    /**
     * Invitation constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $email): void
    {
        $encoder = InvitationEncoder::initEncoder();
        $this->hash = $encoder->encode($email);
    }

    public function getShelfLife(): string
    {
        return $this->shelfLife;
    }

    public function setShelfLife(string $shelfLife): void
    {
        $this->shelfLife = $shelfLife;
    }
}
