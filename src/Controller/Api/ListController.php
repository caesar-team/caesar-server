<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AbstractController;
use App\Entity\Directory\AbstractDirectory;
use App\Entity\Directory\UserDirectory;
use App\Entity\User;
use App\Factory\Entity\Directory\UserDirectoryFactory;
use App\Factory\View\ListViewFactory;
use App\Factory\View\ShortListViewFactory;
use App\Form\Type\Request\User\CreateListRequestType;
use App\Form\Type\Request\User\EditListRequestType;
use App\Form\Type\Request\User\SortListRequestType;
use App\Item\ItemRelocatorInterface;
use App\Model\View\CredentialsList\ListView;
use App\Model\View\CredentialsList\ShortListView;
use App\Modifier\DirectoryModifier;
use App\Repository\DirectoryRepository;
use App\Request\User\CreateListRequest;
use App\Request\User\EditListRequest;
use App\Request\User\SortListRequest;
use App\Security\Voter\ListVoter;
use App\Security\Voter\TeamListVoter;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
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
        return $factory->createCollection($this->getUser()->getDirectoriesWithoutRoot());
    }

    /**
     * @SWG\Tag(name="List")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=CreateListRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success list created",
     *     @Model(type=ListView::class)
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
     */
    public function createListAction(
        Request $request,
        DirectoryRepository $repository,
        UserDirectoryFactory $factory,
        ListViewFactory $viewFactory
    ): ListView {
        $this->denyAccessUnlessGranted(ListVoter::CREATE);

        $createRequest = new CreateListRequest($this->getUser());

        $form = $this->createForm(CreateListRequestType::class, $createRequest);
        $form->submit($request->request->all(), false);
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $list = $factory->createFromRequest($createRequest);
        $repository->save($list);

        return $viewFactory->createSingle($list);
    }

    /**
     * @SWG\Tag(name="List", description="Edit list")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=EditListRequestType::class)
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Success edited list of user",
     *     @Model(type=ListView::class)
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
    public function editListAction(
        Request $request,
        UserDirectory $list,
        ListViewFactory $viewFactory,
        DirectoryModifier $modifier
    ): ListView {
        $this->denyAccessUnlessGranted(ListVoter::EDIT, $list);
        if (null === $list->getParentDirectory()) { //root list
            $message = $this->translator->trans('app.exception.cant_edit_root_list');
            throw new BadRequestHttpException($message);
        }

        $editRequest = new EditListRequest($list);

        $form = $this->createForm(EditListRequestType::class, $editRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $list = $modifier->modifyByRequest($editRequest);

        return $viewFactory->createSingle($list);
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
     */
    public function deleteListAction(
        UserDirectory $list,
        ItemRelocatorInterface $relocator,
        DirectoryRepository $repository
    ): void {
        $this->denyAccessUnlessGranted(ListVoter::DELETE, $list);
        if ($list->isRoot()) {
            throw new BadRequestHttpException($this->translator->trans('app.exception.cant_delete_root_list'));
        }

        $relocator->moveChildItems($list, $list->getUser()->getTrash());
        $repository->remove($list);
    }

    /**
     * Sort List.
     *
     * @SWG\Tag(name="List", description="Sort list")
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     @Model(type=SortListRequestType::class)
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
    public function sortList(AbstractDirectory $list, Request $request, DirectoryModifier $modifier): void
    {
        $this->denyAccessUnlessGranted([ListVoter::SORT, TeamListVoter::SORT], $list);
        if (null === $list->getParentDirectory()) { //root list
            $message = $this->translator->trans('app.exception.cant_edit_root_list');
            throw new BadRequestHttpException($message);
        }

        $sortRequest = new SortListRequest($list);

        $form = $this->createForm(SortListRequestType::class, $sortRequest);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $modifier->modifySortByRequest($sortRequest);
    }

    /**
     * Get available to move lists.
     *
     * @SWG\Tag(name="List")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Full list",
     *     @SWG\Schema(type="array", @Model(type=ShortListView::class))
     * )
     *
     * @Route(
     *     path="/api/lists/movable",
     *     name="api_list_movable",
     *     methods={"GET"}
     * )
     *
     * @return ShortListView[]
     */
    public function availableMoveLists(ShortListViewFactory $factory, DirectoryRepository $repository): array
    {
        return $factory->createCollection($repository->getMovableListsByUser($this->getUser()));
    }
}
