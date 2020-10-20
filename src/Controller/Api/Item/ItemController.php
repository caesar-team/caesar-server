<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Item;
use App\Factory\Entity\ItemFactory;
use App\Factory\View\Item\BatchItemViewFactory;
use App\Factory\View\Item\ItemViewFactory;
use App\Form\Type\Request\Item\CreateBatchItemsRequestType;
use App\Form\Type\Request\Item\CreateBatchKeypairsRequestType;
use App\Form\Type\Request\Item\CreateItemRequestType;
use App\Limiter\Inspector\ItemCountInspector;
use App\Limiter\LimiterInterface;
use App\Limiter\Model\LimitCheck;
use App\Model\View\Item\BatchItemsView;
use App\Model\View\Item\ItemView;
use App\Repository\ItemRepository;
use App\Request\Item\CreateBatchItemsRequest;
use App\Request\Item\CreateBatchKeypairsRequest;
use App\Request\Item\CreateItemRequest;
use App\Security\Voter\ItemVoter;
use App\Security\Voter\TeamItemVoter;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
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
     * Get batch items information.
     *
     * @SWG\Tag(name="Item")
     * @SWG\Response(
     *     response=200,
     *     description="Items data",
     *     @Model(type=BatchItemsView::class)
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="No such item"
     * )
     *
     * @Route(
     *     path="/batch",
     *     name="api_get_item_batch",
     *     methods={"GET"}
     * )
     * @Route(
     *     path="/all",
     *     name="api_get_item_all",
     *     methods={"GET"}
     * )
     */
    public function items(BatchItemViewFactory $factory): BatchItemsView
    {
        return $factory->createSingle($this->getUser());
    }

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
     *     @Model(type=CreateItemRequestType::class)
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
     */
    public function create(
        Request $request,
        ItemViewFactory $viewFactory,
        ItemFactory $factory,
        ItemRepository $itemRepository,
        LimiterInterface $limiter
    ): ItemView {
        $createRequest = new CreateItemRequest($this->getUser());
        $form = $this->createForm(CreateItemRequestType::class, $createRequest, [
            'user' => $this->getUser(),
        ]);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $limiter->check([
            new LimitCheck(ItemCountInspector::class, 1),
        ]);

        $item = $factory->createFromRequest($createRequest);
        $this->denyAccessUnlessGranted([TeamItemVoter::CREATE, ItemVoter::CREATE], $item->getParentList());
        $itemRepository->save($item);

        return $viewFactory->createSingle($item);
    }

    /**
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=CreateBatchItemsRequestType::class)
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
     * @return array<ItemView>
     */
    public function batchCreate(
        Request $request,
        ItemViewFactory $viewFactory,
        ItemFactory $factory,
        ItemRepository $itemRepository,
        LimiterInterface $limiter
    ): array {
        $itemsRequest = new CreateBatchItemsRequest($this->getUser());
        $form = $this->createForm(CreateBatchItemsRequestType::class, $itemsRequest);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $limiter->check([
            new LimitCheck(ItemCountInspector::class, count($itemsRequest->getItems())),
        ]);

        $items = [];
        foreach ($itemsRequest->getItems() as $createItemRequest) {
            $item = $factory->createFromRequest($createItemRequest);
            $this->denyAccessUnlessGranted([TeamItemVoter::CREATE, ItemVoter::CREATE], $item->getParentList());
            $itemRepository->save($item);

            $items[] = $item;
        }

        return $viewFactory->createCollection($items);
    }

    /**
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=CreateBatchKeypairsRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success keypairs created"
     * )
     *
     * @Route(
     *     path="/batch/keypairs",
     *     name="api_batch_create_keypairs_items",
     *     methods={"POST"}
     * )
     */
    public function batchKeypairCreate(
        Request $request,
        ItemViewFactory $viewFactory,
        ItemFactory $factory,
        ItemRepository $itemRepository
    ) {
        $itemsRequest = new CreateBatchKeypairsRequest($this->getUser());
        $form = $this->createForm(CreateBatchKeypairsRequestType::class, $itemsRequest);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $items = [];
        foreach ($itemsRequest->getItems() as $createItemRequest) {
            $item = $factory->createTeamKeypairFromRequest($createItemRequest);
            $this->denyAccessUnlessGranted(TeamItemVoter::CREATE, $item->getParentList());
            if (isset($items[$item->getTeamKeypairGroupKey()])) {
                continue;
            }

            $itemRepository->save($item);

            $items[$item->getTeamKeypairGroupKey()] = $item;
        }

        return $viewFactory->createCollection(array_values($items));
    }
}
