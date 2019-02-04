<?php

declare(strict_types=1);

namespace App\Entity\Audit;

use App\Entity\Item;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AuditItemEventRepository")
 * @ORM\Table(name="audit_events")
 */
class ItemEvent extends AbstractEvent
{
    /**
     * @var Item|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Item", cascade={"persist"})
     * @ORM\JoinColumn(name="target_id", onDelete="SET NULL")
     */
    private $item;

    /**
     * @return Item|null
     */
    public function getItem(): ?Item
    {
        return $this->item;
    }

    /**
     * @param Item|null $item
     */
    public function setItem(?Item $item): void
    {
        $this->item = $item;
    }
}
