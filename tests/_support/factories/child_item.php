<?php

use App\DBAL\Types\Enum\AccessEnumType;
use App\Entity\Team;
use App\Entity\User;
use App\Model\Request\ChildItem;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;

//Doesn`t exist in DB, use only this way: $this->tester->make(ChildItem::class);
/**@var $fm FactoryMuffin */
$fm->define(ChildItem::class)->setDefinitions([
    'owner' => 'entity|' . User::class,
    'secret' => Faker::word(),
    'access' => AccessEnumType::TYPE_READ,
    'link' => null,
    'team' => 'entity|' . Team::class,
]);