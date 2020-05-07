<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Context\ViewFactoryContext;
use App\Controller\AbstractController;
use App\Entity\User;
use App\Model\View\Team\UserTeamView;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

final class UserController extends AbstractController
{
    /**
     * @SWG\Tag(name="User Team")
     *
     * @SWG\Response(
     *     response=200,
     *     description="Teams of user",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type="App\Model\View\Team\UserTeamView")
     *     )
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized"
     * )
     * @Route(path="/api/user/teams", methods={"GET"})
     *
     * @return array|UserTeamView[]
     */
    public function teams(ViewFactoryContext $viewFactoryContext): array
    {
        /** @var User $user */
        $user = $this->getUser();
        $userTeams = $user->getUserTeams();

        return $viewFactoryContext->viewList($userTeams->toArray());
    }
}
