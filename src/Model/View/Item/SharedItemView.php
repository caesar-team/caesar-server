<?php

declare(strict_types=1);

namespace App\Model\View\Item;

use App\Model\View\CredentialsList\ItemView;

class SharedItemView
{
    /**
     * @var string
     */
    public $originalItemId;

    /**
     * @var ItemView[]
     */
    public $items = [];
}
