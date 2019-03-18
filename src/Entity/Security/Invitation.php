<?php

declare(strict_types=1);

namespace App\Entity\Security;

use App\Security\AuthorizationManager\InvitationEncoder;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Class Invitation
 * @ORM\Entity
 */
class Invitation
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
     * @ORM\Column(unique=true, type="text", nullable=false)
     */
    protected $hash;

    /**
     * Invitation constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $email
     */
    public function setHash(string $email): void
    {
        $encoder = InvitationEncoder::initEncoder();
        $this->hash = $encoder->encode($email);
    }
}