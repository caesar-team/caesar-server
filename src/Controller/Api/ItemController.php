<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Item;
use App\Factory\View\CreatedItemViewFactory;
use App\Factory\View\ItemListViewFactory;
use App\Factory\View\ItemViewFactory;
use App\Factory\View\ListTreeViewFactory;
use App\Form\Query\ItemListQueryType;
use App\Form\Request\CreateItemType;
use App\Form\Request\EditItemRequestType;
use App\Form\Request\EditItemType;
use App\Form\Request\Invite\ChildItemCollectionRequestType;
use App\Form\Request\MoveItemType;
use App\Form\Request\SortItemType;
use App\Model\Query\ItemListQuery;
use App\Model\Request\EditItemRequest;
use App\Model\Request\ItemCollectionRequest;
use App\Model\Request\ItemsCollectionRequest;
use App\Model\View\CredentialsList\CreatedItemView;
use App\Model\View\CredentialsList\ItemView;
use App\Model\View\CredentialsList\ListView;
use App\Security\ItemVoter;
use App\Security\ListVoter;
use App\Services\ChildItemHandler;
use App\Utils\DirectoryHelper;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Serializer\SerializerInterface;

final class ItemController extends AbstractController
{
    /**
     * @SWG\Tag(name="Item")
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
     * @param ListTreeViewFactory $viewFactory
     *
     * @return ListView[]
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
     * @param Request $request
     * @param ItemListViewFactory $viewFactory
     *
     * @return ItemView[]|FormInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function itemListAction(Request $request, ItemListViewFactory $viewFactory)
    {
        $itemListQuery = new ItemListQuery();

        $form = $this->createForm(ItemListQueryType::class, $itemListQuery);
        $form->submit($request->query->all());

        if (!$form->isValid()) {
            return $form;
        }
        $this->denyAccessUnlessGranted(ListVoter::SHOW_ITEMS, $itemListQuery->list);

        $itemCollection = $this->getDoctrine()->getRepository(Item::class)->getByQuery($itemListQuery);

        return $viewFactory->create($itemCollection);
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
     * @param Item $item
     * @param ItemViewFactory $factory
     *
     * @return ItemView
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function itemShowAction(Item $item, ItemViewFactory $factory)
    {
        $this->denyAccessUnlessGranted(ItemVoter::SHOW_ITEM, $item);

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
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param CreatedItemViewFactory $viewFactory
     *
     * @return CreatedItemView|FormInterface
     * @throws \Exception
     */
    public function createItemAction(Request $request, EntityManagerInterface $manager, CreatedItemViewFactory $viewFactory)
    {
        $item = new Item();
        $form = $this->createForm(CreateItemType::class, $item);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }
        $this->denyAccessUnlessGranted(ItemVoter::CREATE_ITEM, $item);

        $manager->persist($item);
        $manager->flush();

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
     * @param Item                   $item
     * @param Request                $request
     * @param EntityManagerInterface $manager
     *
     * @return FormInterface|JsonResponse
     */
    public function moveItemAction(Item $item, Request $request, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);
        $oldParentList = $item->getParentList();

        $form = $this->createForm(MoveItemType::class, $item);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }
        $item->setPreviousList($oldParentList);

        $this->denyAccessUnlessGranted(ListVoter::EDIT, $item->getParentList());

        $manager->persist($item);
        $manager->flush();

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
     * @param Item $item
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     *
     * @param SerializerInterface $serializer
     * @param ChildItemHandler $itemHandler
     * @return array|FormInterface
     */
    public function editItem(
        Item $item,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ChildItemHandler $itemHandler
    )
    {
        $this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);
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
     * @param Item                   $item
     * @param EntityManagerInterface $manager
     *
     * @return null
     */
    public function deleteItemAction(Item $item, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted(ItemVoter::DELETE_ITEM, $item);
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
     * @param ItemListViewFactory $viewFactory
     *
     * @return ItemView[]|FormInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
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
     * @param Item $item
     * @param EntityManagerInterface $entityManager
     * @param ItemViewFactory $factory
     *
     * @return ItemView
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function favoriteToggle(Item $item, EntityManagerInterface $entityManager, ItemViewFactory $factory)
    {
        $this->denyAccessUnlessGranted(ItemVoter::SHOW_ITEM, $item);

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
     * @param Item $item
     * @param EntityManagerInterface $entityManager
     * @param ItemViewFactory $factory
     * @param Request $request
     * @return ItemView|FormInterface
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function sort(Item $item, EntityManagerInterface $entityManager, ItemViewFactory $factory, Request $request)
    {
        $this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);

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
     * @param Item $item
     * @param Request $request
     * @param ChildItemHandler $childItemHandler
     *
     * @param ItemViewFactory $viewFactory
     * @return ItemView|FormInterface
     * @throws \Exception
     */
    public function childItemToItem(Item $item, Request $request, ChildItemHandler $childItemHandler, ItemViewFactory $viewFactory)
    {
        $this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);

        $itemCollectionRequest = new ItemCollectionRequest($item);
        $form = $this->createForm(ChildItemCollectionRequestType::class, $itemCollectionRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $items = $childItemHandler->childItemToItem($itemCollectionRequest);

        return $viewFactory->createList($items);
    }

    /**
     * Items collection
     *
     * @SWG\Tag(name="Item")
     * @SWG\Response(
     *     response=200,
     *     description="Items collection",
     * )
     *
     * @Route("/api/offered_item", methods={"GET"}, name="api_item_offered_list")
     * @param ItemListViewFactory $viewFactory
     * @return ItemView[]|array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOfferedItemsList(ItemListViewFactory $viewFactory)
    {
        $user = $this->getUser();
        $offeredItems = DirectoryHelper::extractOfferedItems($user);

        return $viewFactory->create($offeredItems);
    }

    /**
     * @SWG\Tag(name="Item")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=App\Form\Request\AcceptItemsType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Items accepted",
     * )
     *
     * @Route("/api/accept_item", methods={"PATCH"}, name="api_item_accept")
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @return null|FormInterface
     */
    public function acceptList(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        /** @var ItemsCollectionRequest $itemsCollection */
        $itemsCollection = $serializer->deserialize(json_encode($request->request->all()), ItemsCollectionRequest::class, 'json');

        foreach ($itemsCollection->getItems() as $item) {
            $item = $entityManager->getRepository(Item::class)->find($item['id']);
            if ($item instanceof Item) {
                $this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);
                $item->setStatus(Item::STATUS_FINISHED);
            }
        }
        $entityManager->flush();

        $offeredItems = DirectoryHelper::extractOfferedItems($this->getUser());
        foreach ($offeredItems as $offeredItem) {
            $this->denyAccessUnlessGranted(ItemVoter::DELETE_ITEM, $offeredItem);
            $entityManager->remove($offeredItem);
        }

        $entityManager->flush();

        return null;
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
     * @param Item $item
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
     * @param Item $item
     * @param EntityManagerInterface $entityManager
     * @param ItemViewFactory $factory
     *
     * @return null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function acceptItemUpdate(Item $item, EntityManagerInterface $entityManager, ItemViewFactory $factory)
    {
        $this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);

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
     *
     * Decline an item update
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
     * @param Item $item
     * @param EntityManagerInterface $entityManager
     * @param ItemViewFactory $factory
     * @return ItemView
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function declineItemUpdate(Item $item, EntityManagerInterface $entityManager, ItemViewFactory $factory)
    {
        $this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);
        $item->setUpdate(null);

        $entityManager->persist($item);
        $entityManager->flush();

        return $factory->create($item);
    }
}
