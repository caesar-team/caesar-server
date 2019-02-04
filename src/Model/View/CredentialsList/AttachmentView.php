<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use Swagger\Annotations as SWG;

class AttachmentView
{
    /**
     * @var string
     *
     * @SWG\Property(example="logo.png")
     */
    public $name;

    /**
     * @var string
     *
     * @SWG\Property(example="base64 encoded file string")
     */
    public $raw;
}
