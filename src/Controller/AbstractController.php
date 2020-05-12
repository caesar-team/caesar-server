<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Team;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbstractController extends BaseController
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;
    /**
     * @var TeamRepository
     */
    private $teamRepository;

    public function __construct(TranslatorInterface $translator, TeamRepository $teamRepository)
    {
        $this->translator = $translator;
        $this->teamRepository = $teamRepository;
    }

    public function getDefaultTeam(): Team
    {
        return $this->teamRepository->findOneBy(['alias' => Team::DEFAULT_GROUP_ALIAS]);
    }
}
