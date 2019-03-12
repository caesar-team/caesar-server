<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Item;
use App\Entity\ItemMask;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ItemMaskController extends AbstractController
{
    /**
     * @Route("/api/item_mask", methods={"GET"})
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    public function getList(EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $itemMasks = $entityManager->getRepository(ItemMask::class)->findBy(['recipient' => $this->getUser()]);

        if (is_array($itemMasks)) {
            $json = $serializer->serialize($itemMasks,'json');
            return new JsonResponse(json_decode($json));
        }

        return new JsonResponse([], Response::HTTP_OK);
    }

    /**
     * @Route("/api/item_mask/{itemMask}", methods={"POST"})
     *
     * @param ItemMask $itemMask
     * @param SerializerInterface $serializer
     * @return JsonResponse
     * @throws \Exception
     */
    public function create(ItemMask $itemMask, SerializerInterface $serializer): JsonResponse
    {
        $item = $this->createItem($itemMask);
        $this->removeItemMask($itemMask);
        if ($item instanceof Item) {
            $json = $serializer->serialize($item,'json');

            return new JsonResponse(json_decode($json));
        }

        return new JsonResponse([], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/item_mask/{itemMask}", methods={"DELETE"})
     *
     * @param ItemMask $itemMask
     * @return JsonResponse
     */
    public function remove(ItemMask $itemMask): JsonResponse
    {
        $this->removeItemMask($itemMask);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    private function removeItemMask(ItemMask $itemMask): void
    {
        $entityManager = $this->get('doctrine')->getManager();
        $entityManager->remove($itemMask);
        $entityManager->flush();
    }

    /**
     * @param ItemMask $itemMask
     * @return Item
     * @throws \Exception
     */
    private function createItem(ItemMask $itemMask): Item
    {
        $entityManager = $this->get('doctrine')->getManager();
        $item = new Item();
        $item->setParentList($itemMask->getRecipient()->getInbox());
        $item->setOriginalItem($itemMask->getOriginalItem());
        $item->setSecret($itemMask->getSecret());
        $item->setAccess($itemMask->getAccess());
        $item->setType($itemMask->getOriginalItem()->getType());
        $item->refreshLastUpdated();

        $entityManager->persist($item);
        $entityManager->flush();

        return $item;
    }
}