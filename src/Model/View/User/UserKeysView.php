<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Hateoas\Configuration\Annotation as Hateoas;
use Swagger\Annotations as SWG;

/**
 * @Hateoas\Relation(
 *     "keys_save",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route("api_keys_save")
 * )
 */
class UserKeysView
{
    /**
     * @SWG\Property(type="string", example="asdfasdra34w56"))
     */
    private ?string $encryptedPrivateKey;

    /**
     * @SWG\Property(type="string", example="asdfassdaaw46t4wesdra34w56")
     */
    private ?string $publicKey;

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
