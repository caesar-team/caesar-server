<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Factory\View\User\UserViewFactory;
use App\Model\Query\UserListQuery;
use App\Model\View\User\UserView;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/users")
 */
final class ListController extends AbstractController
{
    /**
     * Get list of users.
     *
     * @SWG\Tag(name="User")
     * @SWG\Parameter(
     *     name="ids",
     *     in="query",
     *     description="Users ids",
     *     type="array",
     *     @SWG\Items(type="string")
     * )
     * @SWG\Parameter(
     *     name="role",
     *     in="query",
     *     description="User role",
     *     type="string",
     *     enum={User::ROLE_USER, User::ROLE_ADMIN}
     * )
     * @SWG\Parameter(
     *     name="is_domain_user",
     *     in="query",
     *     description="Is user in domain",
     *     type="boolean",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="List of users",
     *     @SWG\Schema(
     *         type="array",
     *     @Model(type=UserView::class))
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(path="", methods={"GET"})
     *
     * @return UserView[]
     */
    public function users(Request $request, UserRepository $repository, UserViewFactory $factory): array
    {
        return $factory->createCollection(
            $repository->findUsersByQuery(
                new UserListQuery($request)
            )
        );
    }
}
