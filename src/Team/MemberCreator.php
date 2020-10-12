<?php

declare(strict_types=1);

namespace App\Team;

use App\Factory\Entity\MemberFactory;
use App\Mailer\MailRegistry;
use App\Model\DTO\Member;
use App\Notification\MessengerInterface;
use App\Notification\Model\Message;
use App\Repository\UserTeamRepository;
use App\Request\Team\CreateMemberRequest;
use Symfony\Component\Routing\RouterInterface;

class MemberCreator
{
    private MemberFactory $factory;

    private UserTeamRepository $repository;

    private MessengerInterface $messenger;

    private RouterInterface $router;

    public function __construct(
        MemberFactory $factory,
        UserTeamRepository $repository,
        MessengerInterface $messenger,
        RouterInterface $router
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->messenger = $messenger;
        $this->router = $router;
    }

    public function createAndSave(CreateMemberRequest $request): Member
    {
        $member = $this->factory->createFromRequest($request);
        $this->repository->saveMember($member);

        $this->messenger->send(Message::createFromUser(
            $request->getUser(),
            MailRegistry::ADD_TO_TEAM,
            [
                'team_name' => $request->getTeam()->getTitle(),
                'url' => $this->router->generate('root', [], RouterInterface::ABSOLUTE_URL),
            ]
        ));

        return $member;
    }
}
