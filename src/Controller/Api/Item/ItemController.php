<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Item;
use App\Factory\View\Item\ItemViewFactory;
use App\Model\View\Item\ItemView;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/items")
 */
final class ItemController extends AbstractController
{
    /**
     * Get single item information.
     *
     * @SWG\Tag(name="Item")
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
     *     path="/{id}",
     *     name="api_show_item",
     *     methods={"GET"}
     * )
     */
    public function getItem(Item $item, ItemViewFactory $factory): ItemView
    {
        return $factory->createSingle($item);
    }
}
