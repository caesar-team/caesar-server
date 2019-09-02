<?php

declare(strict_types=1);

namespace App\Model\View\Team;

use App\Model\View\CredentialsList\ItemView;
use App\Model\View\CredentialsList\NodeView;

final class ListView extends NodeView
{
    /**
     * @var string
     */
    public $label;
    /**
     * @var ItemView[]
     */
    public $children;
}