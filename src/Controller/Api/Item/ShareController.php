<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Item;
use App\Factory\Entity\ShareFactory;
use App\Factory\View\Item\ShareViewFactory;
use App\Form\Type\Request\Item\ShareBatchItemRequestType;
use App\Model\View\Item\ShareView;
use App\Repository\ItemRepository;
use App\Request\Item\ShareBatchItemRequest;
use App\Security\Voter\ItemVoter;
use App\Security\Voter\TeamItemVoter;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api")
 */
final class ShareController extends AbstractController
{
    /**
     * Shares item.
     *
     * @SWG\Tag(name="Item / Share")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=ShareBatchItemRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Item shared created",
     *     @SWG\Schema(type="array", @Model(type=ShareView::class))
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Shared item not found"
     * )
     * @Route("/items/{item}/share", methods={"POST"}, name="api_item_share")
     *
     * @return ShareView[]
     */
    public function shareItem(
        Request $request,
        Item $item,
        ItemRepository $repository,
        ShareFactory $factory,
        ShareViewFactory $viewFactory
    ): array {
        $this->denyAccessUnlessGranted([ItemVoter::EDIT, TeamItemVoter::EDIT], $item);

        $shareRequest = new ShareBatchItemRequest($item);

        $form = $this->createForm(ShareBatchItemRequestType::class, $shareRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $shares = $factory->createFromRequest($shareRequest);
        $shares = $repository->saveShares(...$shares);

        return $viewFactory->createCollection($shares);
    }

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
