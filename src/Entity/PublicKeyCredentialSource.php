<?php

declare(strict_types=1);

namespace App\Entity;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialSource as BasePublicKeyCredentialSource;
use Webauthn\TrustPath\TrustPath;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PublicKeyCredentialSourceRepository")
 */
class PublicKeyCredentialSource extends BasePublicKeyCredentialSource
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @param string $publicKeyCredentialId
     * @param string $type
     * @param array $transports
     * @param string $attestationType
     * @param TrustPath $trustPath
     * @param string $aaguid
     * @param string $credentialPublicKey
     * @param string $userHandle
     * @param int $counter
     * @throws \Exception
     */
    public function __construct(string $publicKeyCredentialId, string $type, array $transports, string $attestationType, TrustPath $trustPath, string $aaguid, string $credentialPublicKey, string $userHandle, int $counter)
    {
        parent::__construct($publicKeyCredentialId, $type, $transports, $attestationType, $trustPath, $aaguid, $credentialPublicKey, $userHandle, $counter);

        $this->id = Uuid::uuid4();
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @param PublicKeyCredential $publicKeyCredential
     * @param string $userHandle
     * @return BasePublicKeyCredentialSource
     * @throws \Exception
     */
    public static function createFromPublicKeyCredential(PublicKeyCredential $publicKeyCredential, string $userHandle): BasePublicKeyCredentialSource
    {
        $source = BasePublicKeyCredentialSource::createFromPublicKeyCredential($publicKeyCredential, $userHandle);
        $child = new self(
            $source->getPublicKeyCredentialId(),
            $source->getType(),
            $source->getTransports(),
            $source->getAttestationType(),
            $source->getTrustPath(),
            $source->getAaguid(),
            $source->getCredentialPublicKey(),
            $source->getUserHandle(),
            $source->getCounter()
        );

        return $child;
    }
}