<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Item;
use App\Model\Request\BatchItemCollectionRequest;
use App\Model\Request\BatchShareRequest;
use App\Model\Request\ChildItem;
use App\Model\Request\Team\BatchTeamsItemsCollectionRequest;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ItemUpdate;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Mailer\MailRegistry;
use App\Model\DTO\Message;
use App\Model\Request\ItemCollectionRequest;
use Psr\Log\LoggerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\Routing\RouterInterface;

final class ShareManager
{
    const URL_ROOT = 'root';
    const EVENT_NEW_ITEM = 'new';
    const EVENT_UPDATED_ITEM = 'updated';
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @var SenderInterface
     */
    private $sender;
    /**
     * @var RouterInterface
     */
    protected $router;
    /**
     * @var Messenger
     */
    private $messenger;
    /**
     * @var string
     */
    private $absoluteUrl;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        SenderInterface $sender,
        RouterInterface $router,
        Messenger $messenger,
        LoggerInterface $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->sender = $sender;
        $this->router = $router;
        $this->messenger = $messenger;
        $this->absoluteUrl = $this->router->generate(self::URL_ROOT, [], RouterInterface::ABSOLUTE_URL);
        $this->logger = $logger;
    }

    /**
     * @param BatchShareRequest $collectionRequest
     * @return array|Item[]
     * @throws \Exception
     */
    public function share(BatchShareRequest $collectionRequest): array
    {
        $items = [];
        foreach ($collectionRequest->getPersonals() as $personal) {
            $items = array_merge($items, $this->createForPersonal($personal));
        }

        foreach ($collectionRequest->getTeams() as $team) {
            $items = array_merge($items, $this->createForTeam($team));
        }

        return $items;
    }

    /**
     * @param BatchItemCollectionRequest $personal
     * @return array|Item[]
     * @throws \Exception
     */
    private function createForPersonal(BatchItemCollectionRequest $personal): array
    {
        $items = [];
        foreach ($personal->getItems() as $childItem) {
            $item = new Item($childItem->getUser());
            $directory = $childItem->getUser()->getInbox();
            $item->setParentList($directory);
            $item->setOriginalItem($personal->getOriginalItem());
            $item->setSecret($childItem->getSecret());
            $item->setAccess($childItem->getAccess());
            $item->setType($personal->getOriginalItem()->getType());
            $item->setCause($childItem->getCause());
            $item->setStatus($this->getStatusByCause($childItem->getCause()));

            $this->entityManager->persist($item);
            $this->sendItemMessage($childItem);
            $items[$personal->getOriginalItem()->getOriginalItem()->getId()->toString()] = $item;
        }

        return $items;
    }

    private function getStatusByCause(string $cause): string
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

    /**
     * @param ChildItem $childItem
     * @param string $event
     * @throws \Exception
     */
    private function sendItemMessage(ChildItem $childItem, string $event = self::EVENT_NEW_ITEM)
    {
        if ($childItem->getUser()->hasRole(User::ROLE_ANONYMOUS_USER)) {
            return;
        }

        $options = [
            'url' => $this->absoluteUrl,
            'event' => $event,
            'isNotFinishedStatusFlow' => User::FLOW_STATUS_FINISHED !== $childItem->getUser()->getFlowStatus(),
        ];
        $message = new Message($childItem->getUser()->getId()->toString(), $childItem->getUser()->getEmail(), MailRegistry::NEW_ITEM_MESSAGE, $options);
        $this->messenger->send($childItem->getUser(), $message);

        $this->logger->debug('Registered in ChildItemHandler');
    }

    /**
     * @param BatchTeamsItemsCollectionRequest $team
     * @return array|Item[]
     * @throws \Exception
     */
    private function createForTeam(BatchTeamsItemsCollectionRequest $team): array
    {
        $items = [];
        foreach ($team->getShares() as $share) {
            foreach ($share->getItems() as $childItem) {
                $item = new Item($childItem->getUser());
                $item->setParentList($team->getTeam()->getInbox());
                $item->setOriginalItem($share->getOriginalItem());
                $item->setSecret($childItem->getSecret());
                $item->setAccess($childItem->getAccess());
                $item->setType($share->getOriginalItem()->getType());
                $item->setCause($childItem->getCause());
                $item->setStatus($this->getStatusByCause($childItem->getCause()));

                $this->entityManager->persist($item);
                $this->sendItemMessage($childItem);
                $items[$share->getOriginalItem()->getId()->toString()] = $item;
            }
        }

        return $items;
    }
}