<?php

declare(strict_types=1);

namespace App\Controller\Api\Team;

use App\Context\ViewFactoryContext;
use App\Controller\AbstractController;
use App\Entity\User;
use App\Model\View\Team\UserTeamView;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/api/user/teams")
 */
final class UserController extends AbstractController
{
    /**
     * @Route(path="/", methods={"GET"})
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