<?php

declare(strict_types=1);

namespace App\Model\View\Team;

final class UserTeamView
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string|null
     */
    public $title;

    /**
     * @var string|null
     */
    public $type;

    /**
     * @var string|null
     */
    public $userRole;

    /**
     * @var \DateTime
     */
    public $createdAt;

    /**
     * @var \DateTime
     */
    public $updatedAt;

    /**
     * @var string|null
     */
    public $icon;
}
