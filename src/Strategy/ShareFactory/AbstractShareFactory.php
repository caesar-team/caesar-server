<?php

declare(strict_types=1);

namespace App\Strategy\ShareFactory;

use App\Entity\Item;
use App\Services\Messenger;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractShareFactory implements ShareFactoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * InviteHandler constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    final protected function getStatusByCause(string $cause): string
    {
        switch ($cause) {
            case Item::CAUSE_INVITE:
                $status = Item::STATUS_OFFERED;
                break;
            default:
                $status = Item::STATUS_FINISHED;
        }

        return $status;
    }
}