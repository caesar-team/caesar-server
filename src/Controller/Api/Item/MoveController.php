<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Directory;
use App\Entity\Item;
use App\Form\Type\Request\Item\BatchMoveItemsCollectionRequestType;
use App\Form\Type\Request\Item\MoveItemRequestType;
use App\Request\Item\BatchMoveItemsCollectionRequest;
use App\Request\Item\MoveItemRequest;
use App\Security\Voter\ItemVoter;
use App\Security\Voter\ListVoter;
use App\Security\Voter\TeamItemVoter;
use App\Security\Voter\TeamListVoter;
use App\Services\ItemRelocator;
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
 *     description="You are not owner of list or item"
 * )
 */
final class MoveController extends AbstractController
{
    /**
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=MoveItemRequestType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success item moved"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns item move error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="listId",
     *                 @SWG\Items(
     *                     type="string",
     *                     example="This value is not valid."
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @SWG\Response(
     *     response=404,
     *     description="No such item"
     * )
     *
     * @Route(
     *     path="/{id}/move",
     *     name="api_move_item",
     *     methods={"PATCH"}
     * )
     *
     * @throws \Exception
     */
    public function moveItem(
        Item $item,
        Request $request,
        ItemRelocator $itemRelocator
    ): void {
        $this->denyAccessUnlessGranted([ItemVoter::MOVE, TeamItemVoter::MOVE], $item);

        $moveRequest = new MoveItemRequest($item, $this->getUser());

        $form = $this->createForm(MoveItemRequestType::class, $moveRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }
        $this->denyAccessUnlessGranted([ListVoter::MOVABLE, TeamListVoter::MOVABLE], $moveRequest->getList());

        $itemRelocator->move($moveRequest->getList(), $moveRequest);
    }

    /**
     * @SWG\Tag(name="Item")
     * @SWG\Parameter(
     *     name="items",
     *     in="body",
     *     @Model(type=BatchMoveItemsCollectionRequestType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Items moved",
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such directory"
     * )
     *
     * @Route(
     *     path="/batch/move/list/{directory}",
     *     name="api_batch_move_items",
     *     methods={"PATCH"}
     * )
     */
    public function batchMove(
        Request $request,
        Directory $directory,
        ItemRelocator $itemRelocator
    ): void {
        $this->denyAccessUnlessGranted([ListVoter::MOVABLE, TeamListVoter::MOVABLE], $directory);

        $batchRequest = new BatchMoveItemsCollectionRequest($directory);

        $form = $this->createForm(BatchMoveItemsCollectionRequestType::class, $batchRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        foreach ($batchRequest->getMoveItemRequests() as $itemRequest) {
            $this->denyAccessUnlessGranted([ItemVoter::MOVE, TeamItemVoter::MOVE], $itemRequest->getItem());
        }

        $itemRelocator->batchMove($batchRequest);
    }
}
