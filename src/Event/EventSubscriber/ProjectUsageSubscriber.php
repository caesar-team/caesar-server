<?php

declare(strict_types=1);

namespace App\Event\EventSubscriber;

use App\Context\ProjectUsageRegisterContext;
use App\Entity\Item;
use App\Entity\User;
use App\Services\ProjectAuditManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

final class ProjectUsageSubscriber implements EventSubscriber
{
    private const AVAILABLE_CLASSES = [
        Item::class,
        User::class,
    ];
    /**
     * @var ProjectUsageRegisterContext
     */
    private $usageRegisterContext;
    /**
     * @var ProjectAuditManager
     */
    private $projectAuditManager;

    public function __construct(ProjectUsageRegisterContext $usageRegisterContext, ProjectAuditManager $projectAuditManager)
    {
        $this->usageRegisterContext = $usageRegisterContext;
        $this->projectAuditManager = $projectAuditManager;
    }


    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preRemove,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs  $args)
    {
        $object = $args->getObject();
        if (!in_array(get_class($object), self::AVAILABLE_CLASSES)) {
            return;
        }

        $this->usageRegisterContext->registerUsage($object, ProjectUsageRegisterContext::ACTION_UP);
    }

    public function preRemove(LifecycleEventArgs  $args)
    {
        $object = $args->getObject();
        if (!in_array(get_class($object), self::AVAILABLE_CLASSES)) {
            return;
        }

        $this->usageRegisterContext->registerUsage($object, ProjectUsageRegisterContext::ACTION_DOWN);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $object = $args->getObject();

        if (!$object instanceof Item) {
            return;
        }

        $this->projectAuditManager->scanMemory();
    }
}