<?php

use App\Entity\Directory;
use App\Entity\Team;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;

/* @var $fm FactoryMuffin */
$fm->define(Team::class)
    ->setMaker(static function ($class) use ($fm) {
        $object = new $class();

        $lists = Directory::createRootList();
        $lists->setTeam($object);

        $defaultList = Directory::createDefaultList();
        $defaultList->setTeam($object);

        $lists->addChildList($defaultList);

        $trash = Directory::createTrash();
        $trash->setTeam($object);

        $object->setLists($lists);
        $object->setTrash($trash);

        return $object;
    })
    ->setDefinitions([
        'alias' => null,
        'title' => Faker::text(20),
    ])
;
