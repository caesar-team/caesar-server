<?php

namespace App\Tests\Item;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class AnonymousShareTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function shareAndDeleteAnonymous()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $this->tester->have(User::class);
        /** @var User $member */
        $member = $this->tester->have(User::class);

        /** @var User $anonymous */
        $anonymous = $I->have(User::class, ['roles' => [User::ROLE_ANONYMOUS_USER]]);

        $item = $I->createUserItem($user);

        $I->login($user);
        $I->sendPOST(sprintf('items/%s/share', $item->getId()->toString()), [
            'users' => [
                [
                    'userId' => $anonymous->getId()->toString(),
                    'secret' => uniqid(),
                ],
                [
                    'userId' => $member->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$keypairItemId1] = $I->grabDataFromResponseByJsonPath('$[0].keypairId');
        [$keypairItemId2] = $I->grabDataFromResponseByJsonPath('$[1].keypairId');

        $I->sendDELETE(sprintf('items/%s', $keypairItemId1));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->dontSeeInDatabase('fos_user', ['id' => $anonymous->getId()->toString()]);

        $I->sendDELETE(sprintf('items/%s', $keypairItemId2));
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->seeInDatabase('fos_user', ['id' => $member->getId()->toString()]);
    }
}
