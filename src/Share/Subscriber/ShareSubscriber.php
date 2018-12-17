<?php

declare(strict_types=1);

namespace App\Share\Subscriber;

use App\Entity\Share;
use App\Entity\SharePost;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

final class ShareSubscriber implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::preRemove,
            Events::postRemove,
        );
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof Share) {
            return;
        }

        $entityManager = $args->getObjectManager();

        $user = $entity->getUser();
        if ($user->isGuest() && 1 === $user->getAvailableShares()->count()) {
            $entityManager->remove($user);
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof SharePost) {
            return;
        }

        $entityManager = $args->getObjectManager();
        if (0 === $entity->getShare()->getSharedPosts()->count()) {
            $entityManager->remove($entity->getShare());
        }
    }
}
