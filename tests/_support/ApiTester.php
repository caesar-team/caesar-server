<?php

namespace App\Tests;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use Codeception\Util\HttpCode;
use FOS\UserBundle\Model\UserInterface;

/**
 * Inherited Methods.
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    public function login(UserInterface $user): void
    {
        $token = $this->getToken($user);
        $this->setCookie('token', $token);
    }

    public function shareItemToUser(Item $item, User $user, ?Team $team = null): void
    {
        $paramItem = [
            'userId' => $user->getId()->toString(),
            'secret' => 'Some secret string, it doesn`t matter for backend',
            'access' => AccessEnumType::TYPE_READ,
            'cause' => Item::CAUSE_INVITE,
        ];

        if (null !== $team) {
            $paramItem['teamId'] = $team->getId()->toString();
        }

        $this->sendPOST('/items/batch/share',
            [
                'originalItems' => [
                    [
                        'originalItem' => $item->getId()->toString(),
                        'items' => [$paramItem],
                    ],
                ],
            ]
        );
        $this->seeResponseCodeIs(HttpCode::OK);
    }

    public function createTeam(User $user): Team
    {
        /** @var Team $team */
        $team = $this->have(Team::class);
        $this->have(UserTeam::class, [
            'user' => $user,
            'team' => $team,
        ]);

        return $team;
    }

    public function addUserToTeam(Team $team, User $user, string $role = UserTeam::USER_ROLE_MEMBER): void
    {
        $this->have(UserTeam::class, [
            'user' => $user,
            'team' => $team,
            'user_role' => $role,
        ]);
    }
}
