<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Item;
use App\Factory\View\CreatedItemViewFactory;
use App\Factory\View\Item\ItemViewFactory;
use App\Form\Request\CreateItemsType;
use App\Form\Request\CreateItemType;
use App\Limiter\Inspector\ItemCountInspector;
use App\Limiter\LimiterInterface;
use App\Limiter\Model\LimitCheck;
use App\Model\Request\ItemsCollectionRequest;
use App\Model\View\CredentialsList\CreatedItemView;
use App\Model\View\Item\ItemView;
use App\Repository\ItemRepository;
use App\Security\Voter\ItemVoter;
use App\Security\Voter\TeamItemVoter;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/items")
 * @SWG\Response(
 *     response=401,
 *     description="Unauthorized"
 * )
 * @SWG\Response(
 *     response=403,
 *     description="You are not owner of this item"
 * )
 */
final class ItemController extends AbstractController
{
    /**
     * Get single item information.
     *
     * @SWG\Tag(name="Item")
     * @SWG\Response(
     *     response=200,
     *     description="Item data",
     *     @Model(type=ItemView::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="No such item"
     * )
     *
     * @Route(
     *     path="/{id}",
     *     name="api_show_item",
     *     methods={"GET"}
     * )
     */
    public function getItem(Item $item, ItemViewFactory $factory): ItemView
    {
        return $factory->createSingle($item);
    }

    /**
     * Create item.
     *
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=CreateItemType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success item created",
     *     @Model(type=ItemView::class)
     * )
     *
     * @Route(
     *     path="",
     *     name="api_create_item",
     *     methods={"POST"}
     * )
     *
     * @return ItemView|FormInterface
     */
    public function create(
        Request $request,
        ItemViewFactory $viewFactory,
        ItemRepository $itemRepository,
        LimiterInterface $limiter
    ) {
        $item = new Item($this->getUser());
        $form = $this->createForm(CreateItemType::class, $item);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $limiter->check([
            new LimitCheck(ItemCountInspector::class, 1),
        ]);

        $this->denyAccessUnlessGranted([TeamItemVoter::CREATE, ItemVoter::CREATE], $item->getParentList());
        $item->setTeam($item->getParentList()->getTeam());

        $itemRepository->save($item);

        return $viewFactory->createSingle($item);
    }

    /**
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\CreateItemsType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success items created"
     * )
     *
     * @Route(
     *     path="/batch",
     *     name="api_batch_create_items",
     *     methods={"POST"}
     * )
     *
     * @return array<ItemView>|FormInterface
     */
    public function batchCreate(
        Request $request,
        ItemViewFactory $viewFactory,
        ItemRepository $itemRepository,
        LimiterInterface $limiter
    ) {
        $itemsRequest = new ItemsCollectionRequest();

        $form = $this->createForm(CreateItemsType::class, $itemsRequest);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $limiter->check([
            new LimitCheck(ItemCountInspector::class, count($itemsRequest->getItems())),
        ]);

        /** @var Item $item */
        foreach ($itemsRequest->getItems() as $item) {
            $this->denyAccessUnlessGranted([TeamItemVoter::CREATE, ItemVoter::CREATE], $item->getParentList());
            $item->setTeam($item->getParentList()->getTeam());
            $item->setOwner($this->getUser());

            $itemRepository->save($item);
        }

        return $viewFactory->createCollection($itemsRequest->getItems());
    }
}
