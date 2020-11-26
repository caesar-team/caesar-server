<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Item;
use App\Factory\View\Item\ItemViewFactory;
use App\Form\Type\Request\Item\EditItemRequestType;
use App\Model\View\Item\ItemView;
use App\Modifier\ItemModifier;
use App\Request\Item\EditItemRequest;
use App\Security\Voter\ItemVoter;
use App\Security\Voter\TeamItemVoter;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/items")
 */
final class UpdateController extends AbstractController
{
    /**
     * Edit item.
     *
     * @SWG\Tag(name="Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=EditItemRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success item updated",
     *     @Model(type=ItemView::class)
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
     *     path="/{id}",
     *     name="api_edit_item",
     *     methods={"PATCH"}
     * )
     */
    public function edit(
        Item $item,
        Request $request,
        ItemModifier $modifier,
        ItemViewFactory $factory
    ): ItemView {
        $this->denyAccessUnlessGranted([ItemVoter::EDIT, TeamItemVoter::EDIT], $item);

        $editRequest = new EditItemRequest($item);
        $form = $this->createForm(EditItemRequestType::class, $editRequest);
        $form->handleRequest($request);
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $item = $modifier->modifyByRequest($editRequest);

        return $factory->createSingle($item);
    }
}
