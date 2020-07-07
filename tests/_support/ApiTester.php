<?php

namespace App\Tests;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use Codeception\PHPUnit\Constraint\JsonContains;
use Codeception\Util\HttpCode;
use Codeception\Util\JsonArray;
use Doctrine\Common\Collections\ArrayCollection;
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
        $userTeam = $this->have(UserTeam::class, [
            'user' => $user,
            'team' => $team,
        ]);

        $team->addUserTeam($userTeam);

        return $team;
    }

    public function createTeamItem(Team $team, User $user): Item
    {
        /** @var Item $item */
        $item = $this->have(Item::class, [
            'parent_list' => $team->getDefaultDirectory(),
            'owner' => $user,
            'team' => $team,
        ]);

        $sharedItems = [];
        foreach ($team->getUserTeams() as $userTeam) {
            if ($userTeam->getUser()->equals($user)) {
                continue;
            }

            $sharedItems[] = $this->have(Item::class, [
                'parent_list' => $team->getDefaultDirectory(),
                'owner' => $userTeam->getUser(),
                'team' => $team,
                'original_item' => $item,
                'access' => AccessEnumType::TYPE_READ,
                'cause' => Item::CAUSE_INVITE,
            ]);
        }

        $item->setSharedItems(new ArrayCollection($sharedItems));

        return $item;
    }

    public function addUserToTeam(Team $team, User $user, string $role = UserTeam::USER_ROLE_MEMBER): void
    {
        $userTeam = $this->have(UserTeam::class, [
            'user' => $user,
            'team' => $team,
            'user_role' => $role,
        ]);

        $team->addUserTeam($userTeam);
    }

    public function haveUserWithKeys(): User
    {
        return $this->have(User::class, [
            'encrypted_private_key' => uniqid(),
            'public_key' => uniqid(),
        ]);
    }

    public function seeResponseByJsonPathContainsJson(string $jsonPath, array $json = []): void
    {
        \PHPUnit\Framework\Assert::assertThat(
            json_encode($this->grabDataFromResponseByJsonPath($jsonPath)[0]),
            new JsonContains($json)
        );
    }

    public function dontSeeResponseByJsonPathContainsJson(string $jsonPath, array $json = []): void
    {
        $jsonResponseArray = new JsonArray(json_encode($this->grabDataFromResponseByJsonPath($jsonPath)[0]));
        \PHPUnit\Framework\Assert::assertFalse(
            $jsonResponseArray->containsArray($json),
            "Response JSON contains provided JSON\n"
            .'- <info>'.var_export($json, true)."</info>\n"
            .'+ '.var_export($jsonResponseArray->toArray(), true)
        );
    }
}
