<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;

class UserKeysView
{
    /**
     * @var string
     *
     * @SWG\Property(example="asdfasdra34w56")
     */
    public $encryptedPrivateKey;

    /**
     * @var string
     *
     * @SWG\Property(example="asdfassdaaw46t4wesdra34w56")
     */
    public $publicKey;
}
