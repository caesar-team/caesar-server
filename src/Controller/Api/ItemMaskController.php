<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Item;
use App\Entity\ItemMask;
use App\Factory\View\Share\ItemMaskViewFactory;
use App\Model\View\Share\ItemMasksView;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class ItemMaskController extends AbstractController
{
    /**
     * List of offered items
     *
     * @SWG\Tag(name="Item Mask")
     *
     * @SWG\Response(
     *     response=200,
     *     description="List of offered items",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="\App\Model\View\Share\ItemMasksView")
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route("/api/item_mask", methods={"GET"})
     * @param EntityManagerInterface $entityManager
     * @param ItemMaskViewFactory $viewFactory
     * @return \App\Model\View\Share\ItemMasksView|null
     */
    public function getList(EntityManagerInterface $entityManager, ItemMaskViewFactory $viewFactory): ?ItemMasksView
    {
        $itemMasks = $entityManager->getRepository(ItemMask::class)->findBy(['recipient' => $this->getUser()]);

        if (is_array($itemMasks)) {
            return $viewFactory->create($itemMasks);
        }

        return null;
    }

    /**
     *
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