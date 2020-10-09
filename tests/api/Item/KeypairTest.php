<?php

namespace App\Tests\Item;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Item;
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
    public function getKeypairs()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $team = $I->createTeam($user);
        $item = $I->createTeamItem($team, $user);

        /** @var Item $personalKeypair */
        $personalKeypair = $I->have(Item::class, [
            'owner' => $user,
            'type' => NodeEnumType::TYPE_KEYPAIR,
            'parent_list' => $user->getInbox(),
        ]);

        /** @var Item $teamKeypair */
        $teamKeypair = $I->have(Item::class, [
            'owner' => $user,
            'type' => NodeEnumType::TYPE_KEYPAIR,
            'team' => $team,
            'parent_list' => $team->getDefaultDirectory(),
        ]);

        /** @var Item $teamItemKeypair */
        $teamItemKeypair = $I->have(Item::class, [
            'owner' => $user,
            'type' => NodeEnumType::TYPE_KEYPAIR,
            'team' => $team,
            'related_item' => $item,
            'parent_list' => $team->getDefaultDirectory(),
        ]);

        $I->login($user);
        $I->sendGET('/keypairs');

        $schema = $I->getSchema('item/item_list.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContains($personalKeypair->getId()->toString());
        $I->seeResponseContains($teamKeypair->getId()->toString());
        $I->seeResponseContains($teamItemKeypair->getId()->toString());

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

        $I->have(Item::class, [
            'owner' => $user,
            'type' => NodeEnumType::TYPE_KEYPAIR,
            'team' => $team,
            'parent_list' => $team->getDefaultDirectory(),
        ]);
        $I->have(Item::class, [
            'owner' => $user,
            'type' => NodeEnumType::TYPE_KEYPAIR,
            'team' => $otherTeam,
            'parent_list' => $otherTeam->getDefaultDirectory(),
        ]);
        $I->have(Item::class, [
            'owner' => $member,
            'type' => NodeEnumType::TYPE_KEYPAIR,
            'team' => $team,
            'parent_list' => $team->getDefaultDirectory(),
        ]);

        $I->sendGET(sprintf('/keypairs/personal/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);

        $schema = $I->getSchema('item/item.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->sendGET(sprintf('/keypairs/personal/%s', $otherTeam->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->login($member);
        $I->sendGET(sprintf('/keypairs/personal/%s', $team->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->sendGET(sprintf('/keypairs/personal/%s', $otherTeam->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }
}
