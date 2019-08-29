<?php

declare(strict_types=1);

namespace App\Model\View\Team;

final class UserTeamView
{
    /**
     * @var string
     */
    public $teamId;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
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
}