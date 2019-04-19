<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Group;
use App\Factory\View\GroupViewFactory;
use App\Form\Request\Group\CreateGroupType;
use App\Form\Request\Group\EditGroupType;
use App\Model\View\Group\GroupView;
use App\Security\Voter\GroupVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class GroupController extends AbstractController
{
    /**
     * @SWG\Tag(name="Group")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\Group\CreateGroupType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Create a group",
     *     @Model(type=\App\Model\View\Group\GroupView::class)
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/group",
     *     name="api__group_create",
     *     methods={"POST"}
     * )
     *
     *
     * @param Request $request
     * @param GroupViewFactory $viewFactory
     * @param EntityManagerInterface $entityManager
     * @return GroupView
     * @throws \Exception
     */
    public  function create(Request $request, GroupViewFactory $viewFactory, EntityManagerInterface $entityManager): GroupView
    {
        $this->denyAccessUnlessGranted(GroupVoter::GROUP_CREATE, $this->getUser());

        $group = new Group();
        $form = $this->createForm(CreateGroupType::class, $group);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $entityManager->persist($group);
            $entityManager->flush();
        }
        $groupView = $viewFactory->createOne($group);

        return $groupView;
    }

    /**
     * @SWG\Tag(name="Group")
     *
     * @SWG\Response(
     *     response=200,
     *     description="A group view",
     *     @Model(type=\App\Model\View\Group\GroupView::class)
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/group/{group}",
     *     name="api__group_view",
     *     methods={"GET"}
     * )
     *
     *
     * @param Group $group
     * @param GroupViewFactory $viewFactory
     * @return GroupView
     */
    public function group(Group $group, GroupViewFactory $viewFactory)
    {
        $this->denyAccessUnlessGranted(GroupVoter::GROUP_VIEW, $this->getUser());
        $groupView = $viewFactory->createOne($group);

        return $groupView;
    }

    /**
     * @SWG\Tag(name="Group")
     *
     * @SWG\Response(
     *     response=200,
     *     description="List of groups"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/group",
     *     name="api__group_list",
     *     methods={"GET"}
     * )
     *
     *
     * @param GroupViewFactory $viewFactory
     * @param EntityManagerInterface $entityManager
     * @return GroupView[]
     */
    public function groups(GroupViewFactory $viewFactory, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(GroupVoter::GROUP_VIEW, $this->getUser());
        $groups = $entityManager->getRepository(Group::class)->findAll();
        $groupView = $viewFactory->createMany($groups);

        return $groupView;
    }

    /**
     * @SWG\Tag(name="Group")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\Group\CreateGroupType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Edit a group",
     *     @Model(type=\App\Model\View\Group\GroupView::class)
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/group/{group}",
     *     name="api__group_edit",
     *     methods={"PATCH"}
     * )
     *
     *
     * @param Group $group
     * @param Request $request
     * @param GroupViewFactory $viewFactory
     * @param EntityManagerInterface $entityManager
     * @return GroupView
     */
    public function update(Group $group, Request $request, GroupViewFactory $viewFactory, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(GroupVoter::GROUP_EDIT, $this->getUser());

        $form = $this->createForm(EditGroupType::class, $group);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $entityManager->persist($group);
            $entityManager->flush();
        }
        $groupView = $viewFactory->createOne($group);

        return $groupView;
    }

    /**
     * @SWG\Tag(name="Group")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Delete a group"
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(
     *     path="/api/group/{group}",
     *     name="api__group_delete",
     *     methods={"DELETE"}
     * )
     *
     *
     * @param Group $group
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function delete(Group $group, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted(GroupVoter::GROUP_EDIT, $this->getUser());
        $entityManager->remove($group);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}