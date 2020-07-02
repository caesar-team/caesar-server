<?php

use App\Entity\Directory;
use App\Entity\Team;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;

/* @var $fm FactoryMuffin */
$fm->define(Team::class)->setDefinitions([
    'alias' => null,
    'title' => Faker::text(20),
    'lists' => 'entity|'.Directory::class,
    'trash' => 'entity|'.Directory::class,
])->setCallback(function ($object, $saved) {
    if (!$object instanceof Team) {
        return;
    }

    $lists = $object->getLists();
    $lists->setTeam($object);

    $defaultList = Directory::createDefaultList();
    $defaultList->setTeam($object);

    $lists->addChildList($defaultList);

    $object->getTrash()->setTeam($object);
});
