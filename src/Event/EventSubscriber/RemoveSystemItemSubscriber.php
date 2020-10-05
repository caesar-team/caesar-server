<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Item;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RemoveSystemItemSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            AfterEntityDeletedEvent::class => ['preRemove'],
        ];
    }

    public function preRemove(AfterEntityDeletedEvent $event): void
    {
        $item = $event->getEntityInstance();
        if (!$item instanceof Item) {
            return;
        }

        if (NodeEnumType::TYPE_KEYPAIR !== $item->getType()) {
            return;
        }

        if ($item->getRelatedItem()) {
            $this->entityManager->remove($item->getRelatedItem());
            $this->entityManager->flush();

            return;
        }

        if (null === $item->getTeam()) {
            return;
        }

        $userTeam = $item->getTeam()->getUserTeamByUser($item->getSignedOwner());
        if (null !== $userTeam) {
            $this->entityManager->remove($userTeam);

            if (1 === $item->getTeam()->getUserTeams()->count()
                && Team::DEFAULT_GROUP_ALIAS !== $item->getTeam()->getAlias()
            ) {
                $this->entityManager->remove($item->getTeam());
            }
        }

        $this->entityManager->flush();
    }
}
