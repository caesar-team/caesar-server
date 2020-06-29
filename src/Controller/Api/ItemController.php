<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use App\Entity\Item;
use App\Factory\View\CreatedItemViewFactory;
use App\Factory\View\ItemListViewFactory;
use App\Form\Request\CreateItemsType;
use App\Form\Request\CreateItemType;
use App\Form\Request\MoveItemType;
use App\Model\Request\ItemsCollectionRequest;
use App\Model\View\CredentialsList\CreatedItemView;
use App\Repository\ItemRepository;
use App\Repository\TeamRepository;
use App\Security\ItemVoter;
use App\Services\File\ItemMoveResolver;
use App\Utils\DirectoryHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
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
        /** @var ItemsCollectionRequest $itemsCollection */
        $itemsCollection = $serializer->deserialize(json_encode($request->query->all()), ItemsCollectionRequest::class, 'json');

        foreach ($itemsCollection->getItems() as $item) {
            $item = $manager->getRepository(Item::class)->find($item);
            if ($item instanceof Item) {
                //$this->denyAccessUnlessGranted(ItemVoter::DELETE_ITEM, $item);
                if (NodeEnumType::TYPE_TRASH !== $item->getParentList()->getType()) {
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
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Model\Request\ItemsCollectionRequest::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Batch items delete"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/items/batch/delete",
     *     name="api_batch_delete_items_post",
     *     methods={"POST"}
     * )
     *
     * @return null
     */
    public function postBatchDelete(Request $request, EntityManagerInterface $manager, SerializerInterface $serializer)
    {
        /** @var ItemsCollectionRequest $itemsCollection */
        $itemsCollection = $serializer->deserialize(json_encode($request->getContent()), ItemsCollectionRequest::class, 'json');

        foreach ($itemsCollection->getItems() as $item) {
            $item = $manager->getRepository(Item::class)->find($item);
            if ($item instanceof Item) {
                //$this->denyAccessUnlessGranted(ItemVoter::DELETE_ITEM, $item);
                if (NodeEnumType::TYPE_TRASH !== $item->getParentList()->getType()) {
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
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=CreateItemType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success item created",
     *     @Model(type=CreatedItemView::class)
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this list"
     * )
     *
     * @Route(
     *     path="/api/items",
     *     name="api_create_item",
     *     methods={"POST"}
     * )
     *
     * @return CreatedItemView|FormInterface
     */
    public function createItem(
        Request $request,
        CreatedItemViewFactory $viewFactory,
        ItemRepository $itemRepository,
        TeamRepository $teamRepository
    ) {
        $item = new Item($this->getUser());
        $form = $this->createForm(CreateItemType::class, $item);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }
        //$this->denyAccessUnlessGranted(ItemVoter::CREATE_ITEM, $item);
        $team = $teamRepository->findOneByDirectory($item->getParentList());
        $item->setTeam($team);

        $itemRepository->save($item);

        return $viewFactory->createSingle($item);
    }

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
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of list or item"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such item"
     * )
     *
     * @Route(
     *     path="/api/items/{id}/move",
     *     name="api_move_item",
     *     methods={"PATCH"}
     * )
     *
     * @throws NonUniqueResultException
     * @throws \Exception
     */
    public function moveItem(
        Item $item,
        Request $request,
        ItemMoveResolver $itemMoveResolver,
        ItemRepository $itemRepository
    ): ?FormInterface {
        $replacedItem = new Item();

        $form = $this->createForm(MoveItemType::class, $replacedItem);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }
        $replacedItem->setOwner($item->getOwner());

        $itemMoveResolver->move($item, $replacedItem->getParentList());
        $this->denyAccessUnlessGranted(ItemVoter::MOVE_ITEM, $item);
        $itemRepository->flush();

        //$this->denyAccessUnlessGranted(ListVoter::EDIT, $item->getParentList());

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
        //$this->denyAccessUnlessGranted(ItemVoter::DELETE_ITEM, $item);
        if (NodeEnumType::TYPE_TRASH !== $item->getParentList()->getType()) {
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
                //$this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);
                $item->setStatus(Item::STATUS_FINISHED);
            }
        }
        $entityManager->flush();

        $offeredItems = DirectoryHelper::extractOfferedItemsByUser($this->getUser());
        foreach ($offeredItems as $offeredItem) {
            //$this->denyAccessUnlessGranted(ItemVoter::DELETE_ITEM, $offeredItem);
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

    /**
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="itemId",
     *     in="query",
     *     description="Id of item",
     *     type="string"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Item check"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Shared item not found or expired"
     * )
     * @Route("api/anonymous/share/{item}/check", methods={"GET"}, name="api_item_check_shared_item")
     *
     * @return JsonResponse
     */
    public function checkSharedItem(Item $item)
    {
        return new JsonResponse(['id' => $item->getId()->toString()]);
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
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/items/batch",
     *     name="api_batch_create_items",
     *     methods={"POST"}
     * )
     *
     * @throws NonUniqueResultException
     *
     * @return \App\Model\View\Item\ItemView[]|array|FormInterface
     */
    public function batchCreate(
        Request $request,
        ItemListViewFactory $viewFactory,
        ItemRepository $itemRepository,
        TeamRepository $teamRepository
    ) {
        $itemsRequest = new ItemsCollectionRequest();

        $form = $this->createForm(CreateItemsType::class, $itemsRequest);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        /** @var Item $item */
        foreach ($itemsRequest->getItems() as $item) {
            //$this->denyAccessUnlessGranted(ItemVoter::CREATE_ITEM, $item);
            $item->setOwner($this->getUser());
            $team = $teamRepository->findOneByDirectory($item->getParentList());
            $item->setTeam($team);

            $itemRepository->save($item);
        }

        return $viewFactory->create($itemsRequest->getItems());
    }

    /**
     * @SWG\Tag(name="Item")
     * @SWG\Response(
     *     response=204,
     *     description="Items moved",
     * )
     *
     * @Route(
     *     path="/api/items/batch/move/list/{directory}",
     *     name="api_batch_move_items",
     *     methods={"PATCH"}
     * )
     *
     * @throws NonUniqueResultException
     *
     * @return null
     */
    public function batchMove(
        Request $request,
        Directory $directory,
        EntityManagerInterface $manager,
        SerializerInterface $serializer,
        ItemMoveResolver $itemMoveResolver
    ) {
        /** @var ItemsCollectionRequest $itemsCollection */
        $itemsCollection = $serializer->deserialize(json_encode($request->request->all()), ItemsCollectionRequest::class, 'json');

        foreach ($itemsCollection->getItems() as $item) {
            $item = $manager->getRepository(Item::class)->find($item);
            if ($item instanceof Item) {
                $itemMoveResolver->move($item, $directory);
                $this->denyAccessUnlessGranted(ItemVoter::MOVE_ITEM, $item);
            }
        }
        $manager->flush();

        return null;
    }
}
