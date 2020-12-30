<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Directory\AbstractDirectory;
use App\Entity\Item;
use App\Form\Type\Request\Item\BatchMovePersonalItemsType;
use App\Form\Type\Request\Item\MovePersonalItemType;
use App\Item\ItemRelocatorInterface;
use App\Request\Item\BatchMovePersonalItemsRequest;
use App\Request\Item\MovePersonalItemRequest;
use App\Security\Voter\ItemVoter;
use App\Security\Voter\ListVoter;
use App\Security\Voter\TeamListVoter;
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
     *     @Model(type=MovePersonalItemType::class)
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
    public function move(Item $item, Request $request, ItemRelocatorInterface $relocator): void
    {
        $this->denyAccessUnlessGranted(ItemVoter::MOVE, $item);

        $moveRequest = new MovePersonalItemRequest($this->getUser());
        $moveRequest->setItem($item);

        $form = $this->createForm(MovePersonalItemType::class, $moveRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }
        $this->denyAccessUnlessGranted([ListVoter::MOVABLE, TeamListVoter::MOVABLE], $moveRequest->getDirectory());

        $relocator->movePersonalItem($moveRequest);
    }

    /**
     * @SWG\Tag(name="Item")
     * @SWG\Parameter(
     *     name="items",
     *     in="body",
     *     @Model(type=BatchMovePersonalItemsType::class)
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
        AbstractDirectory $directory,
        ItemRelocatorInterface $relocator
    ): void {
        $this->denyAccessUnlessGranted([ListVoter::MOVABLE, TeamListVoter::MOVABLE], $directory);

        $batchRequest = new BatchMovePersonalItemsRequest($directory, $this->getUser());

        $form = $this->createForm(BatchMovePersonalItemsType::class, $batchRequest);
        $form->submit($request->request->all(), false);
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        foreach ($batchRequest->getMoveItemRequests() as $moveItemRequest) {
            $this->denyAccessUnlessGranted(ItemVoter::MOVE, $moveItemRequest->getItem());

            $relocator->movePersonalItem($moveItemRequest);
        }
    }
}
