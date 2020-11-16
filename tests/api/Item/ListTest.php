<?php

namespace App\Tests\Item;

use App\Entity\Directory;
use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ListTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function getSelfLists()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->sendGET('/list');
        $I->seeResponseContainsJson(['type' => Directory::LIST_INBOX]);
        $I->seeResponseContainsJson(['type' => Directory::LIST_TRASH]);
        $I->seeResponseContainsJson(['type' => Directory::LIST_DEFAULT]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('item/lists.json'));
    }

    /** @test */
    public function sortList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var Directory $thirdDirectory */
        $thirdDirectory = $I->have(Directory::class, [
            'label' => '3',
            'user' => $user,
            'sort' => 1,
            'parent_list' => $user->getLists(),
        ]);
        /** @var Directory $secondDirectory */
        $secondDirectory = $I->have(Directory::class, [
            'label' => '2',
            'user' => $user,
            'sort' => 1,
            'parent_list' => $user->getLists(),
        ]);
        /** @var Directory $firstDirectory */
        $firstDirectory = $I->have(Directory::class, [
            'label' => '1',
            'user' => $user,
            'sort' => 1,
            'parent_list' => $user->getLists(),
        ]);

        $I->login($user);
        $I->sendGET('/list');
        $I->seeResponseContainsJson([
            1 => ['id' => $firstDirectory->getId()->toString()],
            2 => ['id' => $secondDirectory->getId()->toString()],
            3 => ['id' => $thirdDirectory->getId()->toString()],
        ]);
    }
}
