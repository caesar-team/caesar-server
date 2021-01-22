<?php

namespace App\Tests\Item;

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\Directory\UserDirectory;
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
        $I->seeResponseContainsJson(['type' => DirectoryEnumType::INBOX]);
        $I->seeResponseContainsJson(['type' => DirectoryEnumType::TRASH]);
        $I->seeResponseContainsJson(['type' => DirectoryEnumType::DEFAULT]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->seeResponseIsValidOnJsonSchemaString($I->getSchema('item/lists.json'));
    }

    /** @test */
    public function sortList()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        /** @var UserDirectory $thirdDirectory */
        $thirdDirectory = $I->have(UserDirectory::class, [
            'label' => '3',
            'user' => $user,
            'sort' => 1,
            'parent_directory' => $user->getLists(),
        ]);
        /** @var UserDirectory $secondDirectory */
        $secondDirectory = $I->have(UserDirectory::class, [
            'label' => '2',
            'user' => $user,
            'sort' => 1,
            'parent_directory' => $user->getLists(),
        ]);
        /** @var UserDirectory $firstDirectory */
        $firstDirectory = $I->have(UserDirectory::class, [
            'label' => '1',
            'user' => $user,
            'sort' => 1,
            'parent_directory' => $user->getLists(),
        ]);

        $I->login($user);
        $I->sendGET('/list');
        $I->seeResponseContainsJson([
           ['id' => $firstDirectory->getId()->toString(), 'sort' => 1],
           ['id' => $secondDirectory->getId()->toString(), 'sort' => 2],
           ['id' => $thirdDirectory->getId()->toString(), 'sort' => 3],
        ]);
    }
}
