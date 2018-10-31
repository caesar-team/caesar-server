<?php

declare(strict_types=1);

namespace App\Model\View\CredentialsList;

use Swagger\Annotations as SWG;

class SecretView
{
    /**
     * @var string
     *
     * @SWG\Property(example="invisionapp.com")
     */
    public $name;

    /**
     * @var string
     *
     * @SWG\Property(example="user1")
     */
    public $login;

    /**
     * @var string
     *
     * @SWG\Property(example="pass1")
     */
    public $pass;

    /**
     * @var string
     *
     * @SWG\Property(example="This is temporary credentials")
     */
    public $note;

    /**
     * @var string|null
     *
     * @SWG\Property(example="https://example.com")
     */
    public $website;

    /**
     * @var AttachmentView[]
     */
    public $attachments = [];
}
