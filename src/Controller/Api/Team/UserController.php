<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Factory\View\Team\UserTeamViewFactory;
use App\Model\View\Team\UserTeamView;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @SWG\Response(
 *     response=401,
 *     description="Unauthorized"
 * )
 */
final class UserController extends AbstractController
{
    /**
     * Teams of user.
     *
     * @SWG\Tag(name="User Team")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Teams of user",
     *     @SWG\Schema(type="array", @Model(type=UserTeamView::class))
     * )
     *
     * @Route(path="/api/user/teams", methods={"GET"})
     *
     * @return UserTeamView[]
     */
    public function teams(UserTeamViewFactory $viewFactory): array
    {
        $user = $this->getUser();

        return $viewFactory->createCollection($user->getUserTeams());
    }
}
