<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Item;
use App\Form\Request\Invite\BatchUpdateChildItemsRequestType;
use App\Form\Request\Invite\InviteUpdateRequestType;
use App\Form\Request\Invite\UpdateChildItemsRequestType;
use App\Model\Request\BatchChildItemsCollectionRequest;
use App\Model\Request\ItemCollectionRequest;
use App\Security\ChildItemVoter;
use App\Services\ChildItemActualizer;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class ChildItemController extends AbstractController
{
    /**
     * @SWG\Tag(name="Child Item")
     *
     * @SWG\Response(
     *     response=204,
     *     description="Success invite revoked"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of parent item"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such item"
     * )
     *
     * @Route(
     *     path="/api/child_item/{id}",
     *     name="api_revoke_child_item",
     *     methods={"DELETE"}
     * )
     *
     * @return null
     */
    public function revokeChildItemAction(Item $item, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(ChildItemVoter::REVOKE_CHILD_ITEM, $item);

        $entityManager->remove($item);
        $entityManager->flush();

        return null;
    }

    /**
     * @SWG\Tag(name="Child Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\Invite\InviteUpdateRequestType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success invite updated"
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
     *
     * @Route(
     *     path="/api/child_item/{id}/access",
     *     name="api_update_child_item",
     *     methods={"PATCH"}
     * )
     *
     * @return FormInterface|null
     */
    public function updateChildItemAccessAction(Item $item, Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(ChildItemVoter::CHANGE_ACCESS, $item);

        $form = $this->createForm(InviteUpdateRequestType::class, $item);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $entityManager->persist($item);
        $entityManager->flush();

        return null;
    }

    /**
     * @SWG\Tag(name="Child Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=App\Form\Request\Invite\BatchUpdateChildItemsRequestType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success items updated"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Returns item share error"
     * )
     *
     * @Route(
     *     path="/api/child_item/batch",
     *     name="api_item_batch_update",
     *     methods={"PATCH"}
     * )
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return FormInterface|null
     */
    public function batchUpdateChildItems(Request $request, ChildItemActualizer $childItemHandler)
    {
        $batchChildItemsCollectionRequest = new BatchChildItemsCollectionRequest();
        $form = $this->createForm(BatchUpdateChildItemsRequestType::class, $batchChildItemsCollectionRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }
        foreach ($batchChildItemsCollectionRequest->getCollectionItems() as $itemCollectionRequest) {
            $this->denyAccessUnlessGranted(ChildItemVoter::UPDATE_CHILD_ITEM, $itemCollectionRequest->getOriginalItem());
            $childItemHandler->updateChildItems($itemCollectionRequest, $this->getUser());
        }

        return null;
    }

    /**
     * Update item with children.
     *
     * @SWG\Tag(name="Child Item")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=App\Form\Request\Invite\UpdateChildItemsRequestType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success item updated"
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
     *
     * @Route(
     *     path="/api/child_item/{id}",
     *     name="api_item_update",
     *     methods={"PATCH"}
     * )
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return FormInterface|null
     */
    public function updateChildItems(Item $item, Request $request, ChildItemActualizer $childItemHandler)
    {
        $this->denyAccessUnlessGranted(ChildItemVoter::UPDATE_CHILD_ITEM, $item);

        $itemCollectionRequest = new ItemCollectionRequest($item);
        $form = $this->createForm(UpdateChildItemsRequestType::class, $itemCollectionRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $childItemHandler->updateChildItems($itemCollectionRequest, $this->getUser());

        return null;
    }
}
