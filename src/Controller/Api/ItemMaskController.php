<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Item;
use App\Entity\ItemMask;
use App\Factory\View\ItemViewFactory;
use App\Factory\View\Share\ItemMaskViewFactory;
use App\Form\Request\CreateItemByMaskType;
use App\Model\View\Share\ItemMasksView;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

        return $viewFactory->create([]);
    }

    /**
     * @SWG\Tag(name="Item Mask")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\CreateItemByMaskType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Item data",
     *     @Model(type="\App\Model\View\CredentialsList\ItemView")
     * )
     *
     * @Route("/api/item_mask/{itemMask}", methods={"POST"})
     *
     * @param Request $request
     * @param ItemMask $itemMask
     * @param ItemViewFactory $viewFactory
     * @return \App\Model\View\CredentialsList\ItemView|\Symfony\Component\Form\FormInterface
     * @throws \Exception
     */
    public function create(Request $request, ItemMask $itemMask, ItemViewFactory $viewFactory)
    {
        $form = $this->createForm(CreateItemByMaskType::class, $itemMask);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $item = $this->createItem($itemMask);
        $this->removeItemMask($itemMask);
        if ($item instanceof Item) {
             return $viewFactory->create($item);
        }

        throw new \LogicException('Unexpected case');
    }

    /**
     * @SWG\Tag(name="Item Mask")
     * @SWG\Response(
     *     response=204,
     *     description="Item mask removed"
     * )
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