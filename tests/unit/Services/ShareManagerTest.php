<?php

namespace App\Tests\Services;

use App\Context\ShareFactoryContext;
use App\Entity\Item;
use App\Model\DTO\ShareItemCollection;
use App\Model\Request\BatchShareRequest;
use App\Services\ShareManager;
use App\Tests\UnitTester;
use Codeception\Test\Unit;

class ShareManagerTest extends Unit
{
    protected UnitTester $tester;

    /**
     * @test
     */
    public function shareEmpty()
    {
        /** @var BatchShareRequest $collectionRequest */
        $collectionRequest = $this->make(BatchShareRequest::class, ['getOriginalItems' => []]);

        $item = $this->makeEmpty(Item::class);
        /** @var ShareFactoryContext $shareFactoryContext */
        $shareFactoryContext = $this->make(ShareFactoryContext::class, ['share' => ['id' => [$item]]]);

        $shareManager = new ShareManager($shareFactoryContext);
        $result = $shareManager->share($collectionRequest);
        $this->assertEquals([], $result);
    }

    /**
     * @test
     */
    public function share()
    {
        $origItem = $this->makeEmpty(Item::class);

        /** @var BatchShareRequest $collectionRequest */
        $collectionRequest = $this->make(BatchShareRequest::class, ['getOriginalItems' => [$origItem, $origItem]]);

        $item = $this->makeEmpty(Item::class);
        /** @var ShareFactoryContext $shareFactoryContext */
        $shareFactoryContext = $this->make(ShareFactoryContext::class, ['share' => ['id' => [$item]]]);

        $shareManager = new ShareManager($shareFactoryContext);
        $result = $shareManager->share($collectionRequest);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(ShareItemCollection::class, $result[0]);
    }
}
