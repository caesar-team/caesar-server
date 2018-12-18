<?php

declare(strict_types=1);

namespace App\Share\Event;

use App\Entity\Share;
use Symfony\Component\EventDispatcher\Event;

final class ShareCreatedEvent extends Event
{
    public const NAME = 'app.share.created';

    /**
     * @var Share
     */
    private $share;

    public function __construct(Share $share)
    {
        $this->share = $share;
    }

    public function getShare(): Share
    {
        return $this->share;
    }
}
