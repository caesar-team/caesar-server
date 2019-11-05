<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class UpdateLog
 * @ORM\Entity()
 */
class UpdateLog extends AbstractNotificationLog
{
    /**
     * @var ItemUpdate
     * @ORM\OneToOne(targetEntity="App\Entity\ItemUpdate", cascade={"remove"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $update;

    public function __construct(ItemUpdate $update)
    {
        parent::__construct();
        $this->update = $update;
    }


    public function getUpdate(): ItemUpdate
    {
        return $this->update;
    }

    public function setUpdate(ItemUpdate $update): void
    {
        $this->update = $update;
    }
}