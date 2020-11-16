<?php

declare(strict_types=1);

namespace App\Controller\Api\User;

use App\Controller\AbstractController;
use App\Factory\View\User\SearchUserViewFactory;
use App\Model\View\User\SearchUserView;
use App\Repository\UserRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/users")
 */
final class SearchController extends AbstractController
{
    /**
     * Search user by part of email.
     *
     * @SWG\Tag(name="User")
     * @SWG\Response(
     *     response=200,
     *     description="List of users",
     *     @SWG\Schema(type="array", @Model(type=SearchUserView::class))
     *
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     *
     * @Route(path="/search", methods={"GET"})
     *
     * @return SearchUserView[]
     */
    public function search(
        Request $request,
        UserRepository $userRepository,
        SearchUserViewFactory $viewFactory
    ): array {
        return $viewFactory->createCollection(
            $userRepository->findByPartOfEmail(
                $request->get('email', '')
            )
        );
    }
}
