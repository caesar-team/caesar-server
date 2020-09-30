<?php

declare(strict_types=1);

namespace App\Strategy\ShareFactory;

use App\Entity\Item;
use App\Entity\User;
use App\Mailer\MailRegistry;
use App\Notification\MessengerInterface;
use App\Notification\Model\Message;
use App\Security\AuthorizationManager\AuthorizationManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractShareFactory implements ShareFactoryInterface
{
    protected const URL_ROOT = 'root';
    protected const EVENT_NEW_ITEM = 'new';
    protected const EVENT_UPDATED_ITEM = 'updated';

    /**
     * @var SenderInterface
     */
    protected $sender;
    /**
     * @var RouterInterface
     */
    protected $router;
    /**
     * @var MessengerInterface
     */
    protected $messenger;
    /**
     * @var string
     */
    protected $absoluteUrl;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    private AuthorizationManager $authorizationManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        SenderInterface $sender,
        RouterInterface $router,
        MessengerInterface $messenger,
        LoggerInterface $logger,
        AuthorizationManager $authorizationManager
    ) {
        $this->entityManager = $entityManager;
        $this->sender = $sender;
        $this->router = $router;
        $this->messenger = $messenger;
        $this->absoluteUrl = $this->router->generate(self::URL_ROOT, [], RouterInterface::ABSOLUTE_URL);
        $this->logger = $logger;
        $this->authorizationManager = $authorizationManager;
    }

    /**
     * @throws \Exception
     */
    final protected function sendItemMessage(Item $item): void
    {
        $owner = $item->getSignedOwner();
        if ($owner->hasRole(User::ROLE_ANONYMOUS_USER)
            || $item->getOriginalItem()->isKeyPairType()
            || $this->authorizationManager->hasInvitation($owner)
        ) {
            return;
        }

        $this->messenger->send(
            Message::createDeferredFromUser(
                $item->getSignedOwner(),
                MailRegistry::SHARE_ITEM,
                ['url' => $this->absoluteUrl, 'share_count' => 1]
            ),
        );

        $this->logger->debug('Registered in ChildItemHandler');
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
