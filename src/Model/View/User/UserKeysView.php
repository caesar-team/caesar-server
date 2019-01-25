<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

class UserKeysView
{
    /**
     * @var string
     *
     * @SWG\Property(example="asdfasdra34w56")
     * @Groups({"key_detail_read"})
     */
    public $encryptedPrivateKey;

    /**
     * @var string
     *
     * @SWG\Property(example="asdfassdaaw46t4wesdra34w56")
     * @Groups({"key_detail_read", "public"})
     */
    public $publicKey;
}
