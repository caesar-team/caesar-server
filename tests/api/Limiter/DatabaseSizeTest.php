<?php

namespace App\Tests\Limiter;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\User;
use App\Limiter\Inspector\DatabaseSizeInspector;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class DatabaseSizeTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    protected function _before()
    {
        $this->tester->executeQuery('TRUNCATE directory CASCADE;');
        $this->tester->executeQuery('TRUNCATE groups CASCADE;');
        $this->tester->executeQuery('TRUNCATE fos_user CASCADE;');
    }

    /** @test */
    public function checkSizeDatabase()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->setLimiterSize(DatabaseSizeInspector::class, 2500000); //in bytes

        $I->login($user);
        $I->haveHttpHeader('Content-Length', 1);
        $I->sendPOST('items', $this->getItemBody($user));
        $I->seeResponseCodeIs(HttpCode::OK);

        // Try to send 30Mb data
        $I->haveHttpHeader('Content-Length', 1024 * 1024 * 30);
        $I->sendPOST('items', $this->getItemBody($user));
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Free space in the database is reached. Contact your Administrator to expand it');

        $I->setLimiterSize(DatabaseSizeInspector::class, -1); ///unlimited

        // Try to send 30Mb data
        $I->haveHttpHeader('Content-Length', 1024 * 1024 * 30);
        $I->sendPOST('items', $this->getItemBody($user));
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    private function getItemBody(User $user, array $options = []): array
    {
        return array_merge([
            'listId' => $user->getDefaultDirectory()->getId()->toString(),
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'title' => 'item title',
            'favorite' => false,
            'tags' => ['tag'],
        ], $options);
    }
}
