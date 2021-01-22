<?php

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\Directory\UserDirectory;
use App\Entity\User;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;

/* @var $fm FactoryMuffin */
$fm->define(UserDirectory::class)
    ->setMaker(static function ($class) use ($fm) {
        return new $class(Faker::text(20)());
    })
    ->setDefinitions([
        'parent_directory' => null,
        'label' => Faker::text(20),
        'type' => DirectoryEnumType::LIST,
        'user' => 'entity|'.User::class,
        'sort' => Faker::numberBetween(0, 20),
    ])
;
