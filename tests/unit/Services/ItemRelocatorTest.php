<?php

namespace App\Tests\Services;

use App\Entity\Directory;
use App\Entity\Item;
use App\Repository\ItemRepository;
use App\Request\Item\MoveItemRequestInterface;
use App\Services\ItemRelocator;
use App\Tests\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;

final class ItemRelocatorTest extends Unit
{
    protected UnitTester $tester;

    /**
     * @var ItemRepository|MockObject
     */
    private $repository;

    private ItemRelocator $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(ItemRepository::class);
        $this->service = new ItemRelocator($this->repository);
    }

    public function canMoveWithoutUpdateSecret()
    {
        $request = $this->createMock(MoveItemRequestInterface::class);

        $directory = $this->createMock(Directory::class);
        $item = $this->createMock(Item::class);

        $request->method('getItem')->willReturn($item);
        $request->method('getSecret')->willReturn(null);

        $item->expects(self::never())->method('setSecret');
        $item->expects(self::once())->method('moveTo')->with($directory);
        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with($item)
        ;

        $this->service->move($directory, $request);
    }

    public function canMoveWithUpdateSecret()
    {
        $request = $this->createMock(MoveItemRequestInterface::class);

        $directory = $this->createMock(Directory::class);
        $item = $this->createMock(Item::class);

        $request->method('getItem')->willReturn($item);
        $request->method('getSecret')->willReturn('secret');

        $item->expects(self::once())->method('setSecret')->with('secret');
        $item->expects(self::once())->method('moveTo')->with($directory);
        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with($item)
        ;

        $this->service->move($directory, $request);
    }
}
