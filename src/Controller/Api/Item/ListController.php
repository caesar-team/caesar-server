<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Item;
use App\Factory\View\ItemListViewFactory;
use App\Form\Type\Query\ItemListQueryType;
use App\Model\Query\ItemListQuery;
use App\Model\View\Item\ItemView;
use App\Repository\ItemRepository;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/items")
 */
final class ListController extends AbstractController
{
    /**
     * Get items by list id.
     *
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
     *     @SWG\Schema(type="array", @Model(type=ItemView::class))
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
     *     path="",
     *     name="api_user_items",
     *     methods={"GET"}
     * )
     *
     * @return array<int, ItemView>
     */
    public function itemList(Request $request, ItemListViewFactory $viewFactory, ItemRepository $repository): array
    {
        $itemListQuery = new ItemListQuery();

        $form = $this->createForm(ItemListQueryType::class, $itemListQuery);
        $form->submit($request->query->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $itemCollection = $repository->getByQuery($itemListQuery);

        return $viewFactory->create($itemCollection);
    }
}
