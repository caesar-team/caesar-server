<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Item;
use App\Entity\ItemMask;
use App\Factory\View\ItemListViewFactory;
use App\Factory\View\ItemViewFactory;
use App\Factory\View\Share\ItemMaskViewFactory;
use App\Form\Request\ItemMasksType;
use App\Model\Request\ItemMaskCollctionRequest;
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
     * @Route("/api/item_mask", methods={"GET"}, name="api_item_mask_list")
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
     * @SWG\Response(
     *     response=200,
     *     description="Item data",
     *     @Model(type="\App\Model\View\CredentialsList\ItemView")
     * )
     *
     * @Route("/api/item_mask/{itemMask}", methods={"POST"}, name="api_item_mask_create")
     *
     * @param ItemMask $itemMask
     * @param ItemViewFactory $viewFactory
     * @return \App\Model\View\CredentialsList\ItemView|\Symfony\Component\Form\FormInterface
     * @throws \Exception
     */
    public function create(ItemMask $itemMask, ItemViewFactory $viewFactory)
    {
        $entityManager = $this->get('doctrine')->getManager();
        $item = $this->createItem($itemMask);
        $entityManager->flush();
        if ($item instanceof Item) {
             return $viewFactory->create($item);
        }

        throw new \LogicException('Unexpected case');
    }

    /**
     * @SWG\Tag(name="Item Mask")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\ItemMasksType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Items list data"
     * )
     * @Route("/api/item_mask", methods={"POST"}, name="api_item_mask_list_create")
     *
     * @param Request $request
     * @param ItemListViewFactory $viewFactory
     * @return \App\Model\View\CredentialsList\ItemView[]|array|\Symfony\Component\Form\FormInterface
     * @throws \Exception
     */
    public function batchCreate(Request $request, ItemListViewFactory $viewFactory)
    {
        $masks = new ItemMaskCollctionRequest();
        $form = $this->createForm(ItemMasksType::class, $masks);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }
        $itemsList = [];
        $entityManager = $this->get('doctrine')->getManager();
        foreach ($masks->getMasks() as $mask) {
            $itemsList[] = $this->createItem($mask->getItemMask(), $entityManager);
        }
        $entityManager->flush();

        return $viewFactory->create($itemsList);
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
        $entityManager = $this->get('doctrine')->getManager();
        $this->removeItemMask($itemMask);
        $entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @SWG\Tag(name="Item Mask")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\ItemMasksType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Item masks removed"
     * )
     *
     * @Route("/api/item_mask", methods={"DELETE"})
     *
     * @param Request $request
     * @return \Symfony\Component\Form\FormInterface
     */
    public function batchRemove(Request $request)
    {
        $masks = new ItemMaskCollctionRequest();
        $form = $this->createForm(ItemMasksType::class, $masks);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }
        $entityManager = $this->get('doctrine')->getManager();
        foreach ($masks->getMasks() as $mask) {
            $this->removeItemMask($mask->getItemMask(), $entityManager);
        }

        $entityManager->flush();
    }

    private function removeItemMask(ItemMask $itemMask, EntityManagerInterface $entityManager = null): void
    {
        $entityManager = $entityManager ?: $this->get('doctrine')->getManager();
        $entityManager->remove($itemMask);
     }

    /**
     * @param ItemMask $itemMask
     * @param EntityManagerInterface|null $entityManager
     * @return Item
     * @throws \Exception
     */
    private function createItem(ItemMask $itemMask, EntityManagerInterface $entityManager = null): Item
    {
        $entityManager = $entityManager ?: $this->get('doctrine')->getManager();
        $item = new Item();
        $item->setParentList($itemMask->getRecipient()->getInbox());
        $item->setOriginalItem($itemMask->getOriginalItem());
        $item->setSecret($itemMask->getSecret());
        $item->setAccess($itemMask->getAccess());
        $item->setType($itemMask->getOriginalItem()->getType());
        $item->refreshLastUpdated();
        $this->removeItemMask($itemMask, $entityManager);

        $entityManager->persist($item);

        return $item;
    }
}