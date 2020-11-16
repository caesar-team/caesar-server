<?php

namespace App\Tests\Item;

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class FavoriteTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function toggleFavorite()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $item = $I->createUserItem($user);

        $I->login($user);
        $I->sendPOST(sprintf('/items/%s/favorite', $item->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['favorite' => true]);
        $I->seeInDatabase('item', ['id' => $item->getId()->toString(), 'favorite' => true]);

        $schema = $I->getSchema('item/favorite.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->sendPOST(sprintf('/items/%s/favorite', $item->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['favorite' => false]);
        $schema = $I->getSchema('item/favorite.json');
        $I->seeResponseIsValidOnJsonSchemaString($schema);

        $I->seeInDatabase('item', ['id' => $item->getId()->toString(), 'favorite' => false]);
    }

    /** @test */
    public function toggleTeamFavorite()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        /** @var User $member */
        $member = $I->have(User::class);

        $team = $I->createTeam($user);
        $I->addUserToTeam($team, $member);

        $item = $I->createTeamItem($team, $user);

        $I->login($user);
        $I->sendPOST(sprintf('/items/%s/favorite', $item->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['favorite' => true]);
        $I->seeInDatabase('item', ['id' => $item->getId()->toString(), 'team_favorite like' => '%'.$user->getId()->toString().'%']);

        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('item/favorite.json'));

        $I->login($member);
        $I->sendGET(sprintf('/items?listId=%s', $team->getDefaultDirectory()->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([0 => ['id' => $item->getId()->toString(), 'favorite' => false]]);

        $I->login($user);
        $I->sendPOST(sprintf('/items/%s/favorite', $item->getId()->toString()));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['favorite' => false]);
        $I->dontSeeInDatabase('item', ['id' => $item->getId()->toString(), 'team_favorite like' => '%'.$user->getId()->toString().'%']);

        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('item/favorite.json'));
    }
}
