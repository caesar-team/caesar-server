<?php

namespace App\Tests\Services;

use App\Context\ShareFactoryContext;
use App\Entity\Item;
use App\Model\Request\BatchShareRequest;
use App\Services\ShareManager;
use App\Tests\UnitTester;
use Codeception\Test\Unit;

class ShareManagerTest extends Unit
{
    protected UnitTester $tester;

    /**
     * @dataProvider shareProvider
     */
    public function testShare($originalItems, $expectedResult)
    {
        /** @var BatchShareRequest $collectionRequest */
        $collectionRequest = $this->make(BatchShareRequest::class, ['getOriginalItems' => $originalItems]);

        $item = $this->makeEmpty(Item::class);
        /** @var ShareFactoryContext $shareFactoryContext */
        $shareFactoryContext = $this->make(ShareFactoryContext::class, ['share' => [$item]]);

        $shareManager = new ShareManager($shareFactoryContext);
        $result = $shareManager->share($collectionRequest);
        $this->assertEquals($expectedResult, $result);
    }

    public function shareProvider(): array
    {
        $item = $this->makeEmpty(Item::class);

        return [
            [[], []],
            [[$item, $item], [$item, $item]],
        ];
    }
}
