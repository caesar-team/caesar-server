<?php

namespace App\Tests\Item;

use App\Entity\User;
use App\Request\Item\KeypairFilterRequest;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class KeypairTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function createKeypairsUniqueRelatedItem()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);
        /** @var User $otherMember */
        $otherMember = $I->have(User::class);
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
                [
                    'userId' => $otherMember->getId()->toString(),
                    'secret' => uniqid(),
                ],
                [
                    'userId' => $user->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        [$keypairItemId1] = $I->grabDataFromResponseByJsonPath('$[1].keypairId');
        [$keypairItemId2] = $I->grabDataFromResponseByJsonPath('$[2].keypairId');

        $I->sendGET(sprintf('items/%s', $item->getId()->toString()));
        $I->seeResponseByJsonPathContainsJson('$.invited', ['id' => $keypairItemId1]);
        $I->seeResponseByJsonPathContainsJson('$.invited', ['id' => $keypairItemId2]);

        $I->sendPOST(sprintf('items/%s/share', $item->getId()->toString()), [
            'users' => [
                [
                    'userId' => $user->getId()->toString(),
                    'secret' => uniqid(),
                ],
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    /** @test */
    public function getKeypairs()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);
        $item = $I->createUserItem($user);
        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);
        $teamItem = $I->createTeamItem($team, $user);

        $personalKeypair = $I->createKeypairItem($user, $item);
        $teamKeypair = $I->createKeypairTeamItem($team, $user);
        $teamItemKeypair = $I->createKeypairTeamItem($team, $member, $teamItem);

        $I->login($user);
        $I->sendGET('/keypairs');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContains($personalKeypair->getId()->toString());
        $I->seeResponseContains($teamKeypair->getId()->toString());
        $I->seeResponseContains($teamItemKeypair->getId()->toString());
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('item/item_list.json'));

        $I->sendGET(sprintf('/keypairs?type=%s', KeypairFilterRequest::TYPE_PERSONAL));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContains($personalKeypair->getId()->toString());
        $I->dontSeeResponseContains($teamKeypair->getId()->toString());
        $I->dontSeeResponseContains($teamItemKeypair->getId()->toString());

        $I->sendGET(sprintf('/keypairs?type=%s', KeypairFilterRequest::TYPE_TEAM));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeResponseContains($personalKeypair->getId()->toString());
        $I->seeResponseContains($teamKeypair->getId()->toString());
        $I->seeResponseContains($teamItemKeypair->getId()->toString());
    }

    /** @test */
    public function getKeypairUserTeam()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);

        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);

        $otherTeam = $I->createTeam($user);

        $I->login($user);
        $I->sendGET(sprintf('/keypairs/personal/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);

        $I->createKeypairTeamItem($team, $user);
        $I->createKeypairTeamItem($otherTeam, $user);
        $I->createKeypairTeamItem($team, $member);

        $I->sendGET(sprintf('/keypairs/personal/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('item/item.json'));

        $I->sendGET(sprintf('/keypairs/personal/%s', $otherTeam->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->login($member);
        $I->sendGET(sprintf('/keypairs/personal/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendGET(sprintf('/keypairs/personal/%s', $otherTeam->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }
}
