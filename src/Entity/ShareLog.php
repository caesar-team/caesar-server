<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ShareLog
 * @ORM\Entity()
 */
class ShareLog extends AbstractNotificationLog
{
    /**
     * @var Item
     * @ORM\OneToOne(targetEntity="App\Entity\Item", cascade={"remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $sharedItem;

    public function __construct(Item $sharedItem)
    {
        parent::__construct();
        $this->sharedItem = $sharedItem;
    }

    public function getSharedItem(): Item
    {
        return $this->sharedItem;
    }

    public function setSharedItem(Item $sharedItem): void
    {
        $this->sharedItem = $sharedItem;
    }
}