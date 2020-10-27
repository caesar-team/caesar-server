<?php

namespace App\Tests\Item;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ShareTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function shareItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $this->tester->have(User::class);
        /** @var User $member */
        $member = $this->tester->have(User::class);
        /** @var User $otherMember */
        $otherMember = $this->tester->have(User::class);

        $item = $I->createUserItem($user);

        $I->login($member);
        $I->sendPOST(sprintf('items/%s/share', $item->getId()->toString()), [
            'users' => [
                [
                    'userId' => $member->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $I->login($user);
        $I->sendPOST(sprintf('items/%s/share', $item->getId()->toString()), [
            'users' => [
                [
                    'userId' => $member->getId()->toString(),
                    'secret' => uniqid(),
                ],
                [
                    'userId' => $otherMember->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('item/shares.json'));

        $I->sendPOST(sprintf('items/%s/share', $item->getId()->toString()), [
            'users' => [
                [
                    'userId' => $member->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Keypair is already exists');
    }
}
