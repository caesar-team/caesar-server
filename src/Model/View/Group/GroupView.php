<?php

declare(strict_types=1);

namespace App\Model\View\Group;

use App\Model\View\User\UserView;

class GroupView
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $alias;
    /**
     * @var string
     */
    public $title;
    /**
     * @var UserView[]
     */
    public $users;
}