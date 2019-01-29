<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Item;
use App\Factory\View\ItemViewFactory;
use App\Form\Request\Invite\InviteCollectionRequestType;
use App\Form\Request\Invite\InviteUpdateRequestType;
use App\Form\Request\Invite\UpdateInvitesRequestType;
use App\Model\Request\InviteCollectionRequest;
use App\Model\View\CredentialsList\ItemView;
use App\Security\InviteVoter;
use App\Security\ItemVoter;
use App\Services\InviteHandler;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

final class InviteController extends AbstractController
{
    /**
     * @SWG\Tag(name="Invite")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=App\Form\Request\Invite\InviteCollectionRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success item shared",
     *     @Model(type=App\Model\View\CredentialsList\ItemView::class)
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
     *     path="/api/item/{id}/invite",
     *     name="api_invite_to_item",
     *     methods={"POST"}
     * )
     *
     * @param Item          $item
     * @param Request       $request
     * @param InviteHandler $inviteHandler
     *
     * @return ItemView|FormInterface
     */
    public function inviteItemAction(Item $item, Request $request, InviteHandler $inviteHandler, ItemViewFactory $viewFactory)
    {
        $this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);

        $inviteCollectionRequest = new InviteCollectionRequest($item);
        $form = $this->createForm(InviteCollectionRequestType::class, $inviteCollectionRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $inviteHandler->inviteToItem($inviteCollectionRequest);

        return $viewFactory->create($item);
    }

    /**
     * @SWG\Tag(name="Invite")
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
     *     path="/api/invite/{id}",
     *     name="api_revoke_invite",
     *     methods={"DELETE"}
     * )
     *
     * @param Item                   $item
     * @param EntityManagerInterface $entityManager
     *
     * @return null
     */
    public function revokeInviteAction(Item $item, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(InviteVoter::REVOKE_INVITE, $item);

        $entityManager->remove($item);
        $entityManager->flush();

        return null;
    }

    /**
     * @SWG\Tag(name="Invite")
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
     *     path="/api/invite/{id}",
     *     name="api_update_invite",
     *     methods={"PATCH"}
     * )
     *
     * @param Item                   $item
     * @param Request                $request
     * @param EntityManagerInterface $entityManager
     *
     * @return FormInterface|null
     */
    public function updateInviteAccessAction(Item $item, Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(InviteVoter::CHANGE_ACCESS, $item);

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
     * @SWG\Tag(name="Invite")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=App\Form\Request\Invite\InviteCollectionRequestType::class)
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
     *     path="/api/item/{id}/invite",
     *     name="api_update_whole_invite",
     *     methods={"PUT"}
     * )
     *
     * @param Item          $item
     * @param Request       $request
     * @param InviteHandler $inviteHandler
     *
     * @return FormInterface|null
     */
    public function updateInviteAction(Item $item, Request $request, InviteHandler $inviteHandler)
    {
        $this->denyAccessUnlessGranted(InviteVoter::UPDATE_INVITE, $item);

        $inviteCollectionRequest = new InviteCollectionRequest($item);
        $form = $this->createForm(UpdateInvitesRequestType::class, $inviteCollectionRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $inviteHandler->updateInvites($inviteCollectionRequest);

        return null;
    }

    /**
     * @SWG\Tag(name="Invite")
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
     * @param Item                   $item
     * @param EntityManagerInterface $entityManager
     * @param ItemViewFactory        $factory
     *
     * @return null
     */
    public function acceptItemUpdate(Item $item, EntityManagerInterface $entityManager, ItemViewFactory $factory)
    {
        $this->denyAccessUnlessGranted(ItemVoter::EDIT_ITEM, $item);

        $update = $item->getUpdate();
        if (null === $update) {
            throw new BadRequestHttpException('Item has no update to accept it');
        }

        $item->setSecret($update->getSecret());
        $item->setUpdate(null);

        $entityManager->persist($item);
        $entityManager->flush();

        return $factory->create($item);
    }
}
