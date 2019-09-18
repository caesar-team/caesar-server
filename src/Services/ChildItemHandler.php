<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Item;
use App\Entity\ItemUpdate;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Mailer\MailRegistry;
use App\Model\DTO\Message;
use App\Model\Request\ChildItem;
use App\Model\Request\ItemCollectionRequest;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Symfony\Component\Routing\RouterInterface;

class ChildItemHandler
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

    /**
     * InviteHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param SenderInterface $sender
     * @param RouterInterface $router
     * @param Messenger $messenger
     * @param LoggerInterface $logger
     */
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
     * @param ItemCollectionRequest $request
     *
     * @return array
     * @throws \Exception
     */
    public function childItemToItem(ItemCollectionRequest $request)
    {
        $items = [];

        $parentList = $request->getOriginalItem()->getParentList();
        $team = $this->entityManager->getRepository(Team::class)->findOneByDirectory($parentList);
        $userTeamRepository = $this->entityManager->getRepository(UserTeam::class);

        foreach ($request->getItems() as $childItem) {
            $userTeam = $userTeamRepository->findOneByUserAndTeam($childItem->getUser(), $team);
            $item = new Item($childItem->getUser());
            $directory = $userTeam ? $parentList : $childItem->getUser()->getInbox();
            $item->setParentList($directory);
            $item->setOriginalItem($request->getOriginalItem());
            $item->setSecret($childItem->getSecret());
            $item->setAccess($childItem->getAccess());
            $item->setType($request->getOriginalItem()->getType());
            $item->setCause($childItem->getCause());
            $item->setStatus($this->getStatusByCause($childItem->getCause()));

            $this->entityManager->persist($item);
            $this->sendItemMessage($childItem);
            $items[] = $item;
        }

        $this->entityManager->flush();
        $this->entityManager->refresh($request->getOriginalItem());

        return $items;
    }

    /**
     * @param ItemCollectionRequest $request
     * @param User $currentOwner
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function updateChildItems(ItemCollectionRequest $request, User $currentOwner): void
    {
        $parentItem = $request->getOriginalItem();
        if (null !== $parentItem->getOriginalItem()) {
            $parentItem = $parentItem->getOriginalItem();
        }

        foreach ($request->getItems() as $childItem) {
            /** @var Item $item */
            /** @var User $user */
            [$item, $user] = $this->getItem($childItem->getUser(), $parentItem);

            if ($currentOwner === $user || Item::CAUSE_SHARE === $item->getCause()) {
                $item->setSecret($childItem->getSecret());
            } else {
                $update = $this->extractUpdate($item, $currentOwner);
                $update->setSecret($childItem->getSecret());
            }
            if ($childItem->getLink()) {
                $item->setLink($childItem->getLink());
            }

            $this->entityManager->persist($item);
            if ($currentOwner !== $user) {
                $this->sendItemMessage($childItem, self::EVENT_UPDATED_ITEM);
            }
        }

        $this->entityManager->flush();
    }

    public function updateItem(Item $item, string $secret, User $currentOwner): void
    {
        $update = $this->extractUpdate($item, $currentOwner);
        $update->setSecret($secret);
        $this->entityManager->persist($update);
    }

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
     * @param User $user
     * @param Item $originalItem
     * @return array
     */
    private function getItem(User $user, Item $originalItem): array
    {
        $owner = $originalItem->getSignedOwner();
        if ($user === $owner) {
            return [$originalItem, $user];
        }

        foreach ($originalItem->getSharedItems() as $sharedItem) {
            $owner = $sharedItem->getSignedOwner();
            if ($user === $owner) {
                return [$sharedItem, $user];
            }
        }

        throw new \LogicException('No Such user in original invite '.$user->getId()->toString());
    }

    public function extractUpdate(Item $item, User $user): ItemUpdate
    {
        if ($item->getUpdate()) {
            return $item->getUpdate();
        }

        return new ItemUpdate($item, $user);
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
}
