<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

class UserKeysView
{
    /**
     * @var string
     * @SWG\Property(example="553d9b8d-fce0-4a53-8cba-f7d334160bc4")
     * @Groups({"public"})
     */
    public $userId;

    /**
     * @var string|null
     *
     * @SWG\Property(example="asdfasdra34w56")
     * @Groups({"key_detail_read"})
     */
    public $encryptedPrivateKey;

    /**
     * @var string|null
     *
     * @SWG\Property(example="asdfassdaaw46t4wesdra34w56")
     * @Groups({"key_detail_read", "public"})
     */
    public $publicKey;

    /**
     * @var string
     * @Groups({"public"})
     */
    public $email;
}
