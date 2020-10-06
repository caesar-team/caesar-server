<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Directory;
use App\Entity\Item;
use App\Form\Request\MoveItemType;
use App\Model\Request\ItemsCollectionRequest;
use App\Repository\ItemRepository;
use App\Security\Voter\ItemVoter;
use App\Security\Voter\ListVoter;
use App\Security\Voter\TeamItemVoter;
use App\Security\Voter\TeamListVoter;
use App\Services\File\ItemMoveResolver;
use Doctrine\ORM\EntityManagerInterface;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

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
     *     @Model(type=\App\Form\Request\MoveItemType::class)
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
        ItemMoveResolver $itemMoveResolver,
        ItemRepository $itemRepository
    ): void {
        $this->denyAccessUnlessGranted([ItemVoter::MOVE, TeamItemVoter::MOVE], $item);

        $replacedItem = new Item();

        $form = $this->createForm(MoveItemType::class, $replacedItem);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $this->denyAccessUnlessGranted([ListVoter::MOVABLE, TeamListVoter::MOVABLE], $replacedItem->getParentList());

        $replacedItem->setOwner($item->getOwner());

        $itemMoveResolver->move($item, $replacedItem->getParentList());
        $itemRepository->flush();
    }

    /**
     * @SWG\Tag(name="Item")
     * @SWG\Parameter(
     *     name="items",
     *     in="body",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="array",
     *             property="items",
     *             @SWG\Items(type="string")
     *         )
     *     )
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
        EntityManagerInterface $manager,
        SerializerInterface $serializer,
        ItemMoveResolver $itemMoveResolver
    ): void {
        $this->denyAccessUnlessGranted([ListVoter::MOVABLE, TeamListVoter::MOVABLE], $directory);

        /** @var ItemsCollectionRequest $itemsCollection */
        $itemsCollection = $serializer->deserialize(json_encode($request->request->all()), ItemsCollectionRequest::class, 'json');

        foreach ($itemsCollection->getItems() as $item) {
            $item = $manager->getRepository(Item::class)->find($item);
            if ($item instanceof Item) {
                $this->denyAccessUnlessGranted([ItemVoter::MOVE, TeamItemVoter::MOVE], $item);
                $itemMoveResolver->move($item, $directory);
            }
        }
        $manager->flush();
    }
}
