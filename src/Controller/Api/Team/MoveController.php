<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Controller\AbstractController;
use App\Entity\Directory\AbstractDirectory;
use App\Entity\Item;
use App\Entity\Team;
use App\Form\Type\Request\Team\BatchMoveTeamItemsType;
use App\Form\Type\Request\Team\MoveTeamItemType;
use App\Item\ItemRelocatorInterface;
use App\Request\Team\BatchMoveTeamItemsRequest;
use App\Request\Team\MoveTeamItemRequest;
use App\Security\Voter\ListVoter;
use App\Security\Voter\TeamItemVoter;
use App\Security\Voter\TeamListVoter;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/teams")
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
     *     @Model(type=MoveTeamItemType::class)
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
     *     path="/{team}/items/{id}/move",
     *     name="api_move_team_item",
     *     methods={"PATCH"}
     * )
     *
     * @throws \Exception
     */
    public function move(Team $team, Item $item, Request $request, ItemRelocatorInterface $relocator): void
    {
        $this->denyAccessUnlessGranted(TeamItemVoter::MOVE, $item);

        if (!$team->equals($item->getTeam())) {
            throw new BadRequestHttpException('The item is not in the team');
        }

        $moveRequest = new MoveTeamItemRequest($team);
        $moveRequest->setItem($item);

        $form = $this->createForm(MoveTeamItemType::class, $moveRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }
        $this->denyAccessUnlessGranted([ListVoter::MOVABLE, TeamListVoter::MOVABLE], $moveRequest->getDirectory());

        $relocator->moveTeamItem($moveRequest);
    }

    /**
     * @SWG\Tag(name="Item")
     * @SWG\Parameter(
     *     name="items",
     *     in="body",
     *     @Model(type=BatchMoveTeamItemsType::class)
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
     *     path="/{team}/batch/move/list/{directory}",
     *     name="api_batch_move_team_items",
     *     methods={"PATCH"}
     * )
     */
    public function batchMove(
        Request $request,
        Team $team,
        AbstractDirectory $directory,
        ItemRelocatorInterface $relocator
    ): void {
        $this->denyAccessUnlessGranted([ListVoter::MOVABLE, TeamListVoter::MOVABLE], $directory);

        $batchRequest = new BatchMoveTeamItemsRequest($directory, $team);

        $form = $this->createForm(BatchMoveTeamItemsType::class, $batchRequest);
        $form->submit($request->request->all(), false);
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        foreach ($batchRequest->getMoveItemRequests() as $moveItemRequest) {
            if (!$team->equals($moveItemRequest->getItem()->getTeam())) {
                throw new BadRequestHttpException(sprintf('The item %s is not in the team', $moveItemRequest->getTeam()->getId()->toString()));
            }

            $this->denyAccessUnlessGranted(TeamItemVoter::MOVE, $moveItemRequest->getItem());
            $relocator->moveTeamItem($moveItemRequest);
        }
    }
}
