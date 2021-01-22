<?php

namespace App\Tests\Item;

use App\Tests\ApiTester;
use Codeception\Module\DataFactory;
use Codeception\Module\REST;
use Codeception\Test\Unit;
use Codeception\Util\HttpCode;

class ConfigTest extends Unit
{
    /**
     * @var ApiTester|REST|DataFactory
     */
    protected ApiTester $tester;

    /** @test */
    public function getConfig()
    {
        $I = $this->tester;

        $I->sendGET('/config');
        $I->seeResponseCodeIs(HttpCode::OK);
    }
}
