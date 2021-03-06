<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Controller\AbstractController;
use App\Entity\Directory;
use App\Entity\Team;
use App\Factory\Entity\TeamDirectoryFactory;
use App\Factory\View\Team\TeamListViewFactory;
use App\Form\Type\Request\Team\CreateListRequestType;
use App\Form\Type\Request\Team\EditListRequestType;
use App\Model\View\Team\TeamListView;
use App\Modifier\DirectoryModifier;
use App\Repository\DirectoryRepository;
use App\Request\Team\CreateListRequest;
use App\Request\Team\EditListRequest;
use App\Security\Voter\TeamListVoter;
use Fourxxi\RestRequestError\Exception\FormInvalidRequestException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/teams")
 * @SWG\Response(
 *     response=401,
 *     description="Unauthorized"
 * )
 */
final class ListController extends AbstractController
{
    /**
     * Get lists by team.
     *
     * @SWG\Tag(name="Team / List")
     * @SWG\Response(
     *     response=200,
     *     description="Team lists",
     *     @SWG\Schema(type="array", @Model(type=TeamListView::class))
     * )
     *
     * @Route(path="/{team}/lists", name="api_team_get_lists", methods={"GET"})
     *
     * @return TeamListView[]
     */
    public function lists(Team $team, TeamListViewFactory $viewFactory): array
    {
        $this->denyAccessUnlessGranted(TeamListVoter::SHOW, $team);

        return $viewFactory->createCollection(
            array_merge(
                [$team->getTrash()],
                $team->getLists()->getChildLists()->toArray()
            )
        );
    }

    /**
     * Create list of team.
     *
     * @SWG\Tag(name="Team / List")
     * @SWG\Response(
     *     response=200,
     *     description="Success created list of team",
     *     @Model(type=TeamListView::class)
     * )
     *
     * @Route(path="/{team}/lists", name="api_team_create_list", methods={"POST"})
     */
    public function create(
        Request $request,
        Team $team,
        TeamDirectoryFactory $factory,
        DirectoryRepository $repository,
        TeamListViewFactory $viewFactory
    ): TeamListView {
        $this->denyAccessUnlessGranted(TeamListVoter::CREATE, $team);

        $createRequest = new CreateListRequest($team);
        $form = $this->createForm(CreateListRequestType::class, $createRequest);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $directory = $factory->createFromRequest($createRequest);
        $repository->save($directory);

        return $viewFactory->createSingle($directory);
    }

    /**
     * Edit list of team.
     *
     * @SWG\Tag(name="Team / List")
     * @SWG\Response(
     *     response=200,
     *     description="Success edited list of team",
     *     @Model(type=TeamListView::class)
     * )
     *
     * @Route(path="/{team}/lists/{list}", name="api_team_edit_list", methods={"PATCH"})
     */
    public function edit(
        Request $request,
        Directory $list,
        DirectoryModifier $modifier,
        TeamListViewFactory $viewFactory
    ): TeamListView {
        $this->denyAccessUnlessGranted(TeamListVoter::EDIT, $list);

        $editRequest = new EditListRequest($list);
        $form = $this->createForm(EditListRequestType::class, $editRequest);

        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new FormInvalidRequestException($form);
        }

        $modifier->modifyByRequest($editRequest);

        return $viewFactory->createSingle($list);
    }

    /**
     * Delete list of team.
     *
     * @SWG\Tag(name="Team / List")
     * @SWG\Response(
     *     response=200,
     *     description="Success edited list of team",
     *     @Model(type=TeamListView::class)
     * )
     *
     * @Route(path="/{team}/lists/{list}", name="api_team_remove_list", methods={"DELETE"})
     */
    public function remove(Directory $list, DirectoryRepository $repository): void
    {
        $this->denyAccessUnlessGranted(TeamListVoter::DELETE, $list);

        $repository->remove($list);
    }
}
