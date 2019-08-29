<?php

declare(strict_types=1);

namespace App\Model\View\Team;

final class MemberView
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $avatar;

    /**
     * @var string
     */
    public $publicKey;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $role;
}