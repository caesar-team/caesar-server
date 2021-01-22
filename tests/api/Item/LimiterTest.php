<?php

namespace App\Tests\Item;

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\User;
use App\Limiter\Inspector\ItemCountInspector;
use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class LimiterTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function limitCreateItemCount()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);
        $I->setLimiterSize(ItemCountInspector::class, 1);

        $I->login($user);
        $I->sendPOST('items', $this->getItemBody($user));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->sendPOST('items', $this->getItemBody($user));
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Maximum number of items is reached. Contact your Administrator');

        $I->setLimiterSize(ItemCountInspector::class, -1);

        $I->sendPOST('items', $this->getItemBody($user));
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    /** @test */
    public function limitCreateBatchItemCount()
    {
        $I = $this->tester;

        /** @var User $user */
        $user = $I->have(User::class);

        $I->login($user);
        $I->setLimiterSize(ItemCountInspector::class, 3);

        $I->sendPOST('items/batch', [
            'items' => [
                $this->getItemBody($user),
                $this->getItemBody($user),
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->sendPOST('items/batch', [
            'items' => [
                $this->getItemBody($user),
                $this->getItemBody($user),
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContains('Maximum number of items is reached. Contact your Administrator');

        $I->setLimiterSize(ItemCountInspector::class, -1);
        $I->sendPOST('items/batch', [
            'items' => [
                $this->getItemBody($user),
                $this->getItemBody($user),
            ],
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    private function getItemBody(User $user, array $options = []): array
    {
        return array_merge([
            'listId' => $user->getDefaultDirectory()->getId()->toString(),
            'type' => NodeEnumType::TYPE_CRED,
            'secret' => uniqid(),
            'meta' => [
                'title' => 'item title',
            ],
            'tags' => ['tag'],
        ], $options);
    }
}
