<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Context\ShareFactoryContext;
use App\Context\ViewFactoryContext;
use App\Controller\AbstractController;
use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use App\Entity\Item;
use App\Entity\User;
use App\Factory\View\BatchListItemViewFactory;
use App\Factory\View\CreatedItemViewFactory;
use App\Factory\View\ItemListViewFactory;
use App\Factory\View\ItemViewFactory;
use App\Factory\View\ListTreeViewFactory;
use App\Form\Query\ItemListQueryType;
use App\Form\Request\BatchShareRequestType;
use App\Form\Request\CreateItemsType;
use App\Form\Request\CreateItemType;
use App\Form\Request\EditItemRequestType;
use App\Form\Request\Invite\ChildItemCollectionRequestType;
use App\Form\Request\MoveItemType;
use App\Form\Request\SortItemType;
use App\Model\DTO\OfferedTeamContainer;
use App\Model\Query\ItemListQuery;
use App\Model\Request\BatchItemCollectionRequest;
use App\Model\Request\BatchShareRequest;
use App\Model\Request\EditItemRequest;
use App\Model\Request\ItemCollectionRequest;
use App\Model\Request\ItemsCollectionRequest;
use App\Model\View\CredentialsList\CreatedItemView;
use App\Model\View\CredentialsList\ItemView;
use App\Model\View\CredentialsList\ListView;
use App\Model\View\CredentialsList\ShareListView;
use App\Model\View\Item\OfferedItemsView;
use App\Model\View\Team\TeamItemsView;
use App\Repository\ItemRepository;
use App\Repository\TeamRepository;
use App\Security\ItemVoter;
use App\Services\ChildItemActualizer;
use App\Services\File\ItemMoveResolver;
use App\Services\ShareManager;
use App\Utils\DirectoryHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use FOS\RestBundle\Controller\Annotations as Rest;
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
     * @SWG\Tag(name="List")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Full list tree with items",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="\App\Model\View\CredentialsList\ListView")
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/list",
     *     name="api_list_tree",
     *     methods={"GET"}
     * )
     *
     * @throws NonUniqueResultException
     *
     * @return ListView[]|array
     */
    public function fullListAction(ListTreeViewFactory $viewFactory)
    {
        return $viewFactory->create($this->getUser());
    }

    /**
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="listId",
     *     in="query",
     *     description="Id of parent list",
     *     type="string"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Item collection",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="\App\Model\View\CredentialsList\ItemView")
     *     )
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
     *     path="/api/item",
     *     name="api_user_items",
     *     methods={"GET"}
     * )
     *
     * @throws NonUniqueResultException
     *
     * @return ItemView[]|FormInterface
     */
    public function itemListAction(Request $request, ItemListViewFactory $viewFactory)
    {
        $itemListQuery = new ItemListQuery();

        $form = $this->createForm(ItemListQueryType::class, $itemListQuery);
        $form->submit($request->query->all());

        if (!$form->isValid()) {
            return $form;
        }
        //todo: CAES-572 permissions refactoring
        //$this->denyAccessUnlessGranted(ListVoter::SHOW_ITEMS, $itemListQuery->list);

        $itemCollection = $this->getDoctrine()->getRepository(Item::class)->getByQuery($itemListQuery);

        return $viewFactory->create($itemCollection);
    }

    /**
     * @SWG\Tag(name="Item")
     * @SWG\Response(
     *     response=204,
     *     description="Items deleted",
     * )
     * @Route(
     *     path="/api/item/batch",
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
     *     path="/api/item/batch/delete",
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
     * @SWG\Response(
     *     response=200,
     *     description="Item data",
     *     @Model(type="\App\Model\View\CredentialsList\ItemView")
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
     *     path="/api/item/{id}",
     *     name="api_show_item",
     *     methods={"GET"}
     * )
     *
     * @throws NonUniqueResultException
     *
     * @return ItemView
     */
    public function itemShowAction(Item $item, ItemViewFactory $factory)
    {
        //$this->denyAccessUnlessGranted(ItemVoter::SHOW_ITEM, $item);

        return $factory->create($item);
    }

    /**
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\CreateItemType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success item created",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="id",
     *             example="f553f7c5-591a-4aed-9148-2958b7d88ee5",
     *         ),
     *         @SWG\Property(
     *             type="string",
     *             property="lastUpdated",
     *             example="Oct 19, 2018 12:08 pm",
     *         )
     *     )
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
     *     path="/api/item",
     *     name="api_create_item",
     *     methods={"POST"}
     * )
     *
     * @throws \Exception
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

        return $viewFactory->create($item);
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
     *     path="/api/item/{id}/move",
     *     name="api_move_item",
     *     methods={"PATCH"}
     * )
     *
     * @throws NonUniqueResultException
     * @throws \Exception
     *
     * @return FormInterface|JsonResponse
     */
    public function moveItem(
        Item $item,
        Request $request,
        ItemMoveResolver $itemMoveResolver,
        ItemRepository $itemRepository
    ) {
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
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\EditItemRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success item edited",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="lastUpdated",
     *             example="Oct 19, 2018 12:08 pm",
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns item edit error",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="object",
     *             property="errors",
     *             @SWG\Property(
     *                 type="array",
     *                 property="secret",
     *                 @SWG\Items(
     *                     type="string",
     *                     example="This value is empty"
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
     *     path="/api/item/{id}",
     *     name="api_edit_item",
     *     methods={"PATCH"}
     * )
     *
     * @return array|FormInterface
     */
    public function editItem(
        Item $item,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ChildItemActualizer $itemHandler
    ) {
        //$this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);
        /** @var EditItemRequest $itemRequest */
        $itemRequest = $serializer->deserialize($request->getContent(), EditItemRequest::class, 'json');
        $item->setSecret($itemRequest->getItem()->getSecret());

        $form = $this->createForm(EditItemRequestType::class, $itemRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $entityManager->persist($item);
        if ($itemRequest->getOriginalItem()->getSecret()) {
            $itemHandler->updateItem($item->getOriginalItem(), $itemRequest->getOriginalItem()->getSecret(), $this->getUser());
        }
        $entityManager->flush();

        return [
            'lastUpdated' => $item->getLastUpdated(),
        ];
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
     *     path="/api/item/{id}",
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
     * Get list of favourite items.
     *
     * @SWG\Tag(name="Item")
     *
     * @SWG\Response(
     *     response=200,
     *     description="List of favourite items"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this item"
     * )
     *
     * @Route(
     *     path="/api/items/favorite",
     *     name="api_favorites_item",
     *     methods={"GET"}
     * )
     *
     * @throws NonUniqueResultException
     *
     * @return ItemView[]|FormInterface
     */
    public function favorite(ItemListViewFactory $viewFactory)
    {
        $itemCollection = $this->getDoctrine()->getRepository(Item::class)->getFavoritesItems($this->getUser());

        return $viewFactory->create($itemCollection);
    }

    /**
     * Toggle favorite item.
     *
     * @SWG\Tag(name="Item")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Set favorite is on or off"
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
     *     path="/api/item/{id}/favorite",
     *     name="api_favorite_item_toggle",
     *     methods={"POST"}
     * )
     *
     * @Rest\View(serializerGroups={"favorite_item"})
     *
     * @throws NonUniqueResultException
     *
     * @return ItemView
     */
    public function favoriteToggle(Item $item, EntityManagerInterface $entityManager, ItemViewFactory $factory)
    {
        //$this->denyAccessUnlessGranted(ItemVoter::SHOW_ITEM, $item);

        $item->setFavorite(!$item->isFavorite());
        $entityManager->persist($item);
        $entityManager->flush();

        return $factory->create($item);
    }

    /**
     * Sort item.
     *
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\SortItemType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Item data",
     *     @Model(type="\App\Model\View\CredentialsList\ItemView")
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
     *     path="/api/item/{item}/sort",
     *     name="api_item_sort",
     *     methods={"PATCH"}
     * )
     *
     * @throws NonUniqueResultException
     *
     * @return ItemView|FormInterface
     */
    public function sort(Item $item, EntityManagerInterface $entityManager, ItemViewFactory $factory, Request $request)
    {
        //$this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);

        $form = $this->createForm(SortItemType::class, $item);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $entityManager->persist($item);
        $entityManager->flush();

        return $factory->create($item);
    }

    /**
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=App\Form\Request\Invite\ChildItemCollectionRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success item shared",
     *     @Model(type=App\Model\View\CredentialsList\ItemView::class, groups={"child_item"})
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
     * @Rest\View(serializerGroups={"child_item"})
     *
     * @Route(
     *     path="/api/item/{id}/child_item",
     *     name="api_child_to_item",
     *     methods={"POST"}
     * )
     *
     * @param ChildItemActualizer $childItemHandler
     *
     * @return ItemView|FormInterface
     */
    public function childItemToItem(
        Item $item,
        Request $request,
        ItemViewFactory $viewFactory,
        ShareFactoryContext $shareFactoryContext
    ) {
        //$this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);

        $itemCollectionRequest = new ItemCollectionRequest($item);
        $form = $this->createForm(ChildItemCollectionRequestType::class, $itemCollectionRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $batchCollectionRequest = new BatchItemCollectionRequest();
        $batchCollectionRequest->setOriginalItem($item);
        $batchCollectionRequest->setItems($itemCollectionRequest->getItems()->toArray());
        $items = $shareFactoryContext->share($batchCollectionRequest);

        return $viewFactory->createList(current($items));
    }

    /**
     * Items collection.
     *
     * @SWG\Tag(name="Item")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Items collection",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="App\Model\View\Item\OfferedItemsView", groups={"offered_item"})
     *     )
     * )
     * @Rest\View(serializerGroups={"offered_item"})
     * @Route("/api/offered_item", methods={"GET"}, name="api_item_offered_list")
     *
     * @return OfferedItemsView
     */
    public function getOfferedItemsList(TeamRepository $teamRepository, ViewFactoryContext $viewFactoryContext)
    {
        /** @var User $user */
        $user = $this->getUser();
        $offeredItems = DirectoryHelper::extractOfferedItemsByUser($user);

        $personalItems = $viewFactoryContext->viewList($offeredItems);
        $teams = $teamRepository->findByUser($user);
        $teamsContainers = OfferedTeamContainer::createMany($teams);
        $teamsItems = $viewFactoryContext->viewList($teamsContainers);

        $teamsItems = array_filter($teamsItems, function (TeamItemsView $teamItemsView) {
            return 0 < count($teamItemsView->items);
        });

        return new OfferedItemsView($personalItems, array_values($teamsItems));
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
            $item = $entityManager->getRepository(Item::class)->find($item['id']);
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
     * @SWG\Response(
     *     response=200,
     *     description="Item data",
     *     @Model(type="\App\Model\View\CredentialsList\ItemView")
     * )
     * @SWG\Response(
     *     response=400,
     *     description="No updates for this item"
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
     *     path="/api/item/{id}/accept_update",
     *     name="api_accept_item_update",
     *     methods={"POST"}
     * )
     *
     * @throws NonUniqueResultException
     *
     * @return null
     */
    public function acceptItemUpdate(Item $item, EntityManagerInterface $entityManager, ItemViewFactory $factory)
    {
        //$this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);

        $update = $item->getUpdate();
        if (null === $update) {
            $message = $this->translator->trans('app.exception.item_has_no_update_to_accept');
            throw new BadRequestHttpException($message);
        }

        $item->setSecret($update->getSecret());
        $item->setUpdate(null);

        $entityManager->persist($item);
        $entityManager->flush();

        return $factory->create($item);
    }

    /**
     * Decline an item update.
     *
     * @SWG\Tag(name="Item")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Item data",
     *     @Model(type="\App\Model\View\CredentialsList\ItemView")
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
     *     path="/api/item/{id}/decline_update",
     *     name="api_decline_item_update",
     *     methods={"POST"}
     * )
     *
     * @throws NonUniqueResultException
     *
     * @return ItemView
     */
    public function declineItemUpdate(Item $item, EntityManagerInterface $entityManager, ItemViewFactory $factory)
    {
        //$this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);
        $item->setUpdate(null);

        $entityManager->persist($item);
        $entityManager->flush();

        return $factory->create($item);
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
     *     path="/api/item/batch",
     *     name="api_batch_create_items",
     *     methods={"POST"}
     * )
     *
     * @throws NonUniqueResultException
     *
     * @return ItemView[]|array|FormInterface
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
     *     path="/api/item/batch/move/list/{directory}",
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

    /**
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=App\Form\Request\BatchShareRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success items shared",
     *     @Model(type="\App\Model\View\CredentialsList\ShareListView")
     * )
     *
     * @Route(
     *     path="/api/item/batch/share",
     *     methods={"POST"}
     * )
     * @Rest\View(serializerGroups={"child_item"})
     *
     * @throws \Exception
     *
     * @return ShareListView|FormInterface
     */
    public function batchShare(
        Request $request,
        ShareManager $shareManager,
        BatchListItemViewFactory $listItemViewFactory
    ) {
        $collectionRequest = new BatchShareRequest();
        $form = $this->createForm(BatchShareRequestType::class, $collectionRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $result = $shareManager->share($collectionRequest);

        return $listItemViewFactory->createList($result);
    }
}
