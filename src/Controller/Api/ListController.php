<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Directory;
use App\Entity\User;
use App\Factory\View\ListViewFactory;
use App\Form\Request\CreateListType;
use App\Form\Request\EditListType;
use App\Form\Request\SortListType;
use App\Model\View\CredentialsList\ListView;
use App\Security\ListVoter;
use App\Services\ItemDisplacer;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @SWG\Response(
 *     response=400,
 *     description="Returns error",
 *     @SWG\Schema(
 *         type="object",
 *         @SWG\Property(
 *             type="object",
 *             property="errors",
 *             @SWG\Property(
 *                 type="array",
 *                 property="label",
 *                 @SWG\Items(
 *                     type="string",
 *                     example="List with such label aleady exist"
 *                 )
 *             )
 *         )
 *     )
 * )
 * @SWG\Response(
 *     response=401,
 *     description="Unauthorized"
 * )
 */
final class ListController extends AbstractController
{
    /**
     * Get lists by user.
     *
     * @SWG\Tag(name="List")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Full list tree with items",
     *     @SWG\Schema(type="array", @Model(type=ListView::class))
     * )
     *
     * @Route(
     *     path="/api/list",
     *     name="api_list_tree",
     *     methods={"GET"}
     * )
     *
     * @return ListView[]
     */
    public function fullList(ListViewFactory $factory): array
    {
        return $factory->createCollection($this->getUser()->getUserPersonalLists());
    }

    /**
     * @SWG\Tag(name="List")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\CreateListType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success item created",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             type="string",
     *             property="id",
     *             example="f553f7c5-591a-4aed-9148-2958b7d88ee5",
     *         )
     *     )
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this list"
     * )
     *
     * @Route(
     *     path="/api/list",
     *     name="api_create_list",
     *     methods={"POST"}
     * )
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return FormInterface|JsonResponse
     */
    public function createListAction(Request $request, EntityManagerInterface $manager)
    {
        $list = new Directory();
        $form = $this->createForm(CreateListType::class, $list);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }
        /** @var User $user */
        $user = $this->getUser();
        $list->setParentList($user->getLists());
        $this->denyAccessUnlessGranted(ListVoter::EDIT, $list->getParentList());

        $manager->persist($list);
        $manager->flush();

        return JsonResponse::create(['id' => $list->getId()->toString()]);
    }

    /**
     * @SWG\Tag(name="List", description="Edit list")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\EditListType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="Success list edited",
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this list"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such list"
     * )
     *
     * @Route(
     *     path="/api/list/{id}",
     *     name="api_edit_list",
     *     methods={"PATCH"}
     * )
     */
    public function editListAction(Directory $list, Request $request, EntityManagerInterface $manager): ?FormInterface
    {
        $this->denyAccessUnlessGranted(ListVoter::EDIT, $list);
        if (null === $list->getParentList()) { //root list
            $message = $this->translator->trans('app.exception.cant_edit_root_list');
            throw new BadRequestHttpException($message);
        }

        $form = $this->createForm(EditListType::class, $list);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $manager->persist($list);
        $manager->flush();

        return null;
    }

    /**
     * @SWG\Tag(name="List")
     *
     * @SWG\Response(
     *     response=204,
     *     description="Success list deleted"
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this list"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such list"
     * )
     *
     * @Route(
     *     path="/api/list/{id}",
     *     name="api_delete_list",
     *     methods={"DELETE"}
     * )
     *
     * @return null
     */
    public function deleteListAction(Directory $list, ItemDisplacer $itemDisplacer, EntityManagerInterface $manager)
    {
        $this->denyAccessUnlessGranted(ListVoter::DELETE, $list);

        if (null === $list->getParentList()) { //root list
            $message = $this->translator->trans('app.exception.cant_delete_root_list');
            throw new BadRequestHttpException($message);
        }

        $itemDisplacer->moveChildItemsToTrash($list, $this->getUser());

        $manager->remove($list);
        $manager->flush();

        return null;
    }

    /**
     * Sort List.
     *
     * @SWG\Tag(name="List", description="Sort list")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=\App\Form\Request\SortListType::class)
     * )
     * @SWG\Response(
     *     response=204,
     *     description="List position changed",
     * )
     * @SWG\Response(
     *     response=403,
     *     description="You are not owner of this list"
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No such list"
     * )
     *
     * @Route(
     *     path="/api/list/{id}/sort",
     *     name="api_sort_list",
     *     methods={"PATCH"}
     * )
     */
    public function sortList(Directory $list, Request $request, EntityManagerInterface $manager): ?FormInterface
    {
        $this->denyAccessUnlessGranted(ListVoter::SORT, $list);
        if (null === $list->getParentList()) { //root list
            $message = $this->translator->trans('app.exception.cant_edit_root_list');
            throw new BadRequestHttpException($message);
        }

        $form = $this->createForm(SortListType::class, $list);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            return $form;
        }

        $manager->persist($list);
        $manager->flush();

        return null;
    }
}
