<?php

namespace App\Tests;

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Item;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\UserTeam;
use App\Security\TwoFactor\BackUpCodesManager;
use Codeception\PHPUnit\Constraint\JsonContains;
use Codeception\Util\HttpCode;
use Codeception\Util\JsonArray;
use FOS\UserBundle\Model\UserInterface;
use League\FactoryMuffin\Faker\Facade as Faker;
use Ramsey\Uuid\Uuid;

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

    public function get2FAHashCode(string $code): string
    {
        $encoder = BackUpCodesManager::initEncoder();

        return $encoder->encode($code);
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

    public function createDefaultTeam(): Team
    {
        return $this->have(Team::class, [
            'alias' => Team::DEFAULT_GROUP_ALIAS,
            'title' => Team::DEFAULT_GROUP_ALIAS,
        ]);
    }

    public function releaseUsername(string $username): void
    {
        $clearEmail = Faker::email()();
        $this->updateInDatabase('fos_user', [
            'username' => $clearEmail,
            'username_canonical' => $clearEmail,
            'email' => $clearEmail,
            'email_canonical' => $clearEmail,
        ], ['username' => $username]);
    }

    public function setLimiterSize(string $inspector, int $size): void
    {
        $this->executeQuery('DELETE FROM system_limit WHERE inspector = ?', [$inspector]);
        $this->haveInDatabase('system_limit', ['id' => Uuid::uuid4()->toString(), 'limit_size' => $size, 'inspector' => $inspector]);
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
        return $this->have(Item::class, [
            'parent_list' => $team->getDefaultDirectory(),
            'owner' => $user,
            'team' => $team,
        ]);
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

    public function haveUserWithKeys(array $params = []): User
    {
        return $this->have(User::class, array_merge([
            'encrypted_private_key' => uniqid(),
            'public_key' => uniqid(),
        ], $params));
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
