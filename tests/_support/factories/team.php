<?php

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use App\Entity\Team;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;

/* @var $fm FactoryMuffin */
$fm->define(Team::class)->setDefinitions([
    'alias' => null,
    'title' => Faker::text(20),
])->setCallback(function ($object, $saved) {
    if (!$object instanceof Team) {
        return;
    }

    $lists = Directory::createRootList();
    $lists->setTeam($object);

    $defaultList = Directory::createDefaultList();
    $defaultList->setTeam($object);

    $lists->addChildList($defaultList);

    $trash = Directory::createTrash();

    $trash->setTeam($object);
    $trash->setType(NodeEnumType::TYPE_TRASH);

    $object->setLists($lists);
    $object->setTrash($trash);
});
