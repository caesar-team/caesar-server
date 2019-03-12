<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Item;
use App\Entity\ItemMask;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ItemMaskController extends AbstractController
{
    public function getList(EntityManagerInterface $entityManager): JsonResponse
    {
        $itemMasks = $entityManager->getRepository(ItemMask::class)->findBy(['recipient' => $this->getUser()]);
        dump($itemMasks); die;

        return new JsonResponse();
    }

    /**
     * @param ItemMask $itemMask
     * @return JsonResponse
     * @throws \Exception
     */
    public function create(ItemMask $itemMask): JsonResponse
    {
        $this->createItem($itemMask);
        $this->removeItemMask($itemMask);
        return new JsonResponse();
    }

    public function remove(ItemMask $itemMask): JsonResponse
    {
        $this->removeItemMask($itemMask);
        return new JsonResponse();
    }

    private function removeItemMask(ItemMask $itemMask): void
    {
        $entityManager = $this->get('doctrine')->getManager();
        $entityManager->remove($itemMask);
        $entityManager->flush();
    }

    /**
     * @param ItemMask $itemMask
     * @throws \Exception
     */
    private function createItem(ItemMask $itemMask)
    {
        $entityManager = $this->get('doctrine')->getManager();
        $item = new Item();
        $item->setParentList($itemMask->getRecipient()->getInbox());
        $item->setOriginalItem($itemMask->getOriginalItem());
        $item->setSecret($itemMask->getSecret());
        $item->setAccess($itemMask->getAccess());
        $item->setType($itemMask->getOriginalItem()->getType());

        $entityManager->flush();
    }
}