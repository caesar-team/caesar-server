<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Item;
use App\Factory\View\Item\ItemViewFactory;
use App\Form\Request\SortItemType;
use App\Model\View\Item\ItemView;
use App\Repository\ItemRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/items")
 */
final class SortController extends AbstractController
{
    /**
     * Sort item.
     *
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=SortItemType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Item data",
     *     @Model(type=ItemView::class)
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
     *     path="/{id}/sort",
     *     name="api_item_sort",
     *     methods={"PATCH"}
     * )
     *
     * @return ItemView|FormInterface
     */
    public function sort(Item $item, ItemRepository $repository, ItemViewFactory $factory, Request $request)
    {
        $form = $this->createForm(SortItemType::class, $item);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $repository->save($item);

        return $factory->createSingle($item);
    }
}
