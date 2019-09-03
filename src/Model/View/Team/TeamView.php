<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Model\View\User\UserView;

class TeamView
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $title;
    /**
     * @var UserView[]
     */
    public $users;
    /**
     * @var ListView[]
     */
    public $lists;

    /**
     * @var string
     */
    public $icon;
}