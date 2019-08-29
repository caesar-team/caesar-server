<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Context\ViewFactoryContext;
use App\Controller\AbstractController;
use App\Entity\User;
use App\Model\View\Team\UserTeamView;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

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
     * @param ViewFactoryContext $viewFactoryContext
     * @return array|UserTeamView[]
     */
    public function teams(ViewFactoryContext $viewFactoryContext)
    {
        /** @var User $user */
        $user = $this->getUser();
        $teams = $user->getUserTeams();

        return $viewFactoryContext->viewList($teams->toArray());
    }
}