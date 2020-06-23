<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Controller\AbstractController;
use App\Factory\View\User\UserViewFactory;
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
     * @SWG\Response(
     *     response=200,
     *     description="List of users",
     *     @SWG\Schema(
     *         type="array",
     *     @Model(type=UserView::class))
     * )
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
    public function users(Request $request, UserRepository $userRepository, UserViewFactory $viewFactory): array
    {
        $ids = $request->query->get('ids', []);

        $users = !empty($ids)
            ? $userRepository->findByIds($ids)
            : $userRepository->findAllExceptAnonymous()
        ;

        return $viewFactory->createCollection($users);
    }
}
