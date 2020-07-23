<?php

declare(strict_types=1);

namespace App\Strategy\ShareFactory;

use App\Entity\Item;
use App\Entity\User;
use App\Mailer\MailRegistry;
use App\Model\Request\ChildItem;
use App\Notification\MessengerInterface;
use App\Notification\Model\Message;
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

    /**
     * InviteHandler constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SenderInterface $sender,
        RouterInterface $router,
        MessengerInterface $messenger,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->sender = $sender;
        $this->router = $router;
        $this->messenger = $messenger;
        $this->absoluteUrl = $this->router->generate(self::URL_ROOT, [], RouterInterface::ABSOLUTE_URL);
        $this->logger = $logger;
    }

    /**
     * @throws \Exception
     */
    final protected function sendItemMessage(ChildItem $childItem, string $event = self::EVENT_NEW_ITEM): void
    {
        if ($childItem->getUser()->hasRole(User::ROLE_ANONYMOUS_USER)) {
            return;
        }

        $this->messenger->send(
            Message::createDeferredFromUser(
                $childItem->getUser(),
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
