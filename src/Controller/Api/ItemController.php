<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Item;
use App\Model\Request\ItemsCollectionRequest;
use App\Repository\ItemRepository;
use App\Repository\TeamRepository;
use App\Security\Voter\ItemVoter;
use App\Security\Voter\TeamItemVoter;
use App\Utils\DirectoryHelper;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ItemController extends AbstractController
{
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
     *     description="Items deleted",
     * )
     * @Route(
     *     path="/api/items/batch",
     *     name="api_batch_delete_items",
     *     methods={"DELETE"}
     * )
     *
     * @return null
     */
    public function batchDelete(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer)
    {
        $query = $request->query->all();
        if (empty($query)) {
            $query = $request->request->all();
        }

        /** @var ItemsCollectionRequest $itemsCollection */
        $itemsCollection = $serializer->deserialize(json_encode($query), ItemsCollectionRequest::class, 'json');

        foreach ($itemsCollection->getItems() as $item) {
            $item = $manager->getRepository(Item::class)->find($item);
            if ($item instanceof Item) {
                $this->denyAccessUnlessGranted([ItemVoter::DELETE, TeamItemVoter::DELETE], $item);
                if ($item->isNotDeletable()) {
                    $message = $this->translator->trans('app.exception.delete_trash_only');
                    throw new BadRequestHttpException($message);
                }

                $manager->remove($item);
            }
        }
        $manager->flush();

        return null;
    }

    /**
     * @SWG\Tag(name="Item")
     *
     * @SWG\Response(
     *     response=204,
     *     description="Success item deleted"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns item deletion error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="array",
     *             property="errors",
     *             @SWG\Items(
     *                 type="string",
     *                 example="You can fully delete item only from trash"
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
     *     description="You are not owner of this item"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such item"
     * )
     *
     * @Route(
     *     path="/api/items/{id}",
     *     name="api_delete_item",
     *     methods={"DELETE"}
     * )
     *
     * @return null
     */
    public function deleteItem(Item $item, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted([ItemVoter::DELETE, TeamItemVoter::DELETE], $item);
        if ($item->isNotDeletable()) {
            $message = $this->translator->trans('app.exception.delete_trash_only');
            throw new BadRequestHttpException($message);
        }

        $manager->remove($item);
        $manager->flush();

        return null;
    }

    /**
     * @SWG\Tag(name="Item")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=App\Form\Request\AcceptItemsType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Items accepted",
     * )
     *
     * @Route("/api/accept_item", methods={"PATCH"}, name="api_item_accept")
     *
     * @return FormInterface|null
     */
    public function acceptList(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        /** @var ItemsCollectionRequest $itemsCollection */
        $itemsCollection = $serializer->deserialize(json_encode($request->request->all()), ItemsCollectionRequest::class, 'json');

        foreach ($itemsCollection->getItems() as $item) {
            // TODO check and fix $item['id']
            $item = $entityManager->getRepository(Item::class)->find($item['id'] ?? $item);
            if ($item instanceof Item) {
                $this->denyAccessUnlessGranted(ItemVoter::EDIT, $item);
                $item->setStatus(Item::STATUS_FINISHED);
            }
        }
        $entityManager->flush();

        $offeredItems = DirectoryHelper::extractOfferedItemsByUser($this->getUser());
        foreach ($offeredItems as $offeredItem) {
            $this->denyAccessUnlessGranted(ItemVoter::DELETE, $offeredItem);
            $entityManager->remove($offeredItem);
        }

        $entityManager->flush();

        return null;
    }

    /**
     * @SWG\Tag(name="Item")
     *
     * @SWG\Response(
     *     response=204,
     *     description="Items accepted",
     * )
     * @Route("/api/accept_teams_items", methods={"PATCH"})
     *
     * @return JsonResponse
     */
    public function acceptTeamsItems(TeamRepository $teamRepository, ItemRepository $itemRepository)
    {
        $teams = $teamRepository->findByUser($this->getUser());

        foreach ($teams as $team) {
            $items = DirectoryHelper::extractOfferedTeamsItemsByUser($this->getUser(), $team);

            array_walk($items, function (Item $item) use ($itemRepository) {
                $item->setStatus(Item::STATUS_FINISHED);
                $itemRepository->save($item);
            });
        }

        return new JsonResponse(['success' => true], Response::HTTP_NO_CONTENT);
    }
}
