<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @Hateoas\Relation(
 *     "keys_save",
 *     attributes={"method": "POST"},
 *     href=@Hateoas\Route(
 *         "api_keys_save"
 *     ),
 *     exclusion=@Hateoas\Exclusion(groups={"key_detail_read"})
 * )
 */
class UserKeysView
{
    /**
     * @var string
     * @SWG\Property(type="string", example="553d9b8d-fce0-4a53-8cba-f7d334160bc4")
     * @Groups({"public"})
     * @Serializer\Groups({"public"})
     */
    public $userId;

    /**
     * @var string|null
     *
     * @SWG\Property(type="string", example="asdfasdra34w56")
     * @Groups({"key_detail_read"})
     * @Serializer\Groups({"key_detail_read"})
     */
    public $encryptedPrivateKey;

    /**
     * @var string|null
     *
     * @SWG\Property(type="string", example="asdfassdaaw46t4wesdra34w56")
     * @Groups({"key_detail_read", "public"})
     * @Serializer\Groups({"key_detail_read", "public"})
     */
    public $publicKey;

    /**
     * @var string
     *
     * @SWG\Property(type="string", example="email@email")
     * @Groups({"public"})
     * @Serializer\Groups({"public"})
     */
    public $email;
}
