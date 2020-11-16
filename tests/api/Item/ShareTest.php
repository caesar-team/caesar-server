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
                    'userId' => $user->getId()->toString(),
                    'secret' => uniqid(),
                ],
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

        $I->sendGET('/items/all');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeResponseByJsonPathContainsJson('$.shares', ['id' => $item->getId()->toString()]);
        $I->seeResponseByJsonPathContainsJson('$.personals', ['id' => $item->getId()->toString()]);
    }

    /** @test */
    public function overridingRelatedItemData()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $this->tester->have(User::class);
        /** @var User $member */
        $member = $this->tester->have(User::class);

        $item = $I->createUserItem($user);

        $I->login($user);
        $I->sendPOST(sprintf('/items/%s/favorite', $item->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->sendPOST(sprintf('items/%s/share', $item->getId()->toString()), [
            'users' => [
                [
                    'userId' => $member->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->login($member);
        $I->sendGET('/items/all');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseByJsonPathContainsJson('$.shares.0', ['favorite' => false]);
    }

    /** @test */
    public function lastUpdatedRelatedItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $this->tester->have(User::class);
        /** @var User $member */
        $member = $this->tester->have(User::class);

        $item = $I->createUserItem($user);

        $I->login($user);
        $I->sendPOST(sprintf('items/%s/share', $item->getId()->toString()), [
            'users' => [
                [
                    'userId' => $user->getId()->toString(),
                    'secret' => uniqid(),
                ],
                [
                    'userId' => $member->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $lastUpdatedTime = time() + 1;

        sleep(2);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPATCH(sprintf('items/%s', $item->getId()->toString()), [
            'secret' => 'secret-edit',
            'title' => 'item title (edited)',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendGET(sprintf('/items/all?lastUpdated=%s', $lastUpdatedTime));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseByJsonPathContainsJson('$.personals', ['id' => $item->getId()->toString()]);

        $I->login($member);
        $I->sendGET(sprintf('/items/all?lastUpdated=%s', $lastUpdatedTime));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseByJsonPathContainsJson('$.shares', ['id' => $item->getId()->toString()]);
    }
}
