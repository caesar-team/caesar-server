<?php

declare(strict_types=1);

namespace App\Model\View\Share;

use App\Model\View\User\UserView;

class ItemMaskView
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $secret;
    /**
     * @var UserView
     */
    public $recipient;
    /**
     * @var string
     */
    public $originalItem;
    /**
     * @var string
     */
    public $access;
}