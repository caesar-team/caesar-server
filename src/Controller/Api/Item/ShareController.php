<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Context\ShareFactoryContext;
use App\Controller\AbstractController;
use App\Entity\Item;
use App\Factory\View\Item\LinkedItemViewFactory;
use App\Factory\View\Share\ShareListViewFactory;
use App\Form\Request\BatchShareRequestType;
use App\Form\Request\Invite\ChildItemCollectionRequestType;
use App\Model\Request\BatchItemCollectionRequest;
use App\Model\Request\BatchShareRequest;
use App\Model\Request\ItemCollectionRequest;
use App\Model\View\Item\LinkedItemView;
use App\Model\View\Share\ShareListView;
use App\Services\ShareManager;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api")
 */
final class ShareController extends AbstractController
{
    /**
     * Create linked items to item.
     *
     * @SWG\Tag(name="Item / Share")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=ChildItemCollectionRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success item shared",
     *     @SWG\Schema(
     *         type="object",
     *         properties={
     *             @SWG\Property(
     *                 property="items",
     *                 @SWG\Schema(type="array", @Model(type=LinkedItemView::class))
     *             )
     *         }
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns item share error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="userId",
     *                 @SWG\Items(
     *                     type="string",
     *                     example="This value is not valid"
     *                 )
     *             )
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of item"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such item"
     * )
     *
     * @Route(
     *     path="/items/{id}/child_item",
     *     name="api_child_to_item",
     *     methods={"POST"}
     * )
     */
    public function createChildItemToItem(
        Item $item,
        Request $request,
        LinkedItemViewFactory $viewFactory,
        ShareFactoryContext $shareFactoryContext
    ): array {
        $itemCollectionRequest = new ItemCollectionRequest($item);
        $form = $this->createForm(ChildItemCollectionRequestType::class, $itemCollectionRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $batchCollectionRequest = new BatchItemCollectionRequest();
        $batchCollectionRequest->setOriginalItem($item);
        $batchCollectionRequest->setItems($itemCollectionRequest->getItems()->toArray());
        $items = $shareFactoryContext->share($batchCollectionRequest);

        //@todo @frontend remove `items` to frontend and return LinkedItemView[]
        return ['items' => $viewFactory->createCollection(current($items))];
    }

    /**
     * Batch create linked items.
     *
     * @SWG\Tag(name="Item / Share")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=App\Form\Request\BatchShareRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success items shared",
     *     @Model(type=ShareListView::class)
     * )
     *
     * @Route(
     *     path="/items/batch/share",
     *     name="api_batch_share_item",
     *     methods={"POST"}
     * )
     */
    public function batchShare(
        Request $request,
        ShareManager $shareManager,
        ShareListViewFactory $viewFactory
    ): ShareListView {
        $collectionRequest = new BatchShareRequest();
        $form = $this->createForm(BatchShareRequestType::class, $collectionRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        //@todo @frontend remove `shares` to frontend and return ShareView[]
        return $viewFactory->createSingle(
            $shareManager->share($collectionRequest)
        );
    }

    /**
     * Check share item.
     *
     * @SWG\Tag(name="Item / Share")
     * @SWG\Response(
     *     response=200,
     *     description="Item check"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Shared item not found or expired"
     * )
     * @Route("/anonymous/share/{item}/check", methods={"GET"}, name="api_item_check_shared_item")
     *
     * @return JsonResponse
     */
    public function checkSharedItem(Item $item)
    {
        return new JsonResponse(['id' => $item->getId()->toString()]);
    }
}
