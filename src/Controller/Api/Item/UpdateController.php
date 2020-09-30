<?php

declare(strict_types=1);

namespace App\Controller\Api\Item;

use App\Controller\AbstractController;
use App\Entity\Item;
use App\Factory\View\Item\ItemViewFactory;
use App\Form\Request\EditItemType;
use App\Model\View\Item\ItemView;
use App\Repository\ItemRepository;
use App\Security\Voter\ItemVoter;
use App\Security\Voter\TeamItemVoter;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormInterface;
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
     *     @Model(type=EditItemType::class)
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
     *
     * @return ItemView|FormInterface
     */
    public function edit(
        Item $item,
        Request $request,
        ItemRepository $repository,
        ItemViewFactory $factory
    ) {
        $this->denyAccessUnlessGranted([ItemVoter::EDIT, TeamItemVoter::EDIT], $item);

        $form = $this->createForm(EditItemType::class, $item);
        $form->submit($request->request->all(), false);
        if (!$form->isValid()) {
            return $form;
        }

        $repository->save($item);

        return $factory->createSingle($item);
    }
}
