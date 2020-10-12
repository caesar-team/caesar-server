<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Item;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api")
 */
final class ShareController extends AbstractController
{
    /**
     * Check share item.
     *
     * @SWG\Tag(name="Item / Share")
     * @SWG\Response(
     *     response=200,
     *     description="Item check"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Shared item not found or expired"
     * )
     * @Route("/anonymous/share/{item}/check", methods={"GET"}, name="api_item_check_shared_item")
     *
     * @return JsonResponse
     */
    public function checkSharedItem(Item $item)
    {
        return new JsonResponse(['id' => $item->getId()->toString()]);
    }
}
