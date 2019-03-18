<?php

declare(strict_types=1);

namespace App\Model\View\Share;

use App\Model\View\User\UserView;
use Symfony\Component\Serializer\Annotation\Groups;

class ItemMaskView
{
    /**
     * @var string
     * @Groups({"create_child_item"})
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