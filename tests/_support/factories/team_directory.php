<?php

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\Directory\TeamDirectory;
use App\Entity\Team;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;

/* @var $fm FactoryMuffin */
$fm->define(TeamDirectory::class)
    ->setMaker(static function ($class) use ($fm) {
        return new $class(Faker::text(20)());
    })
    ->setDefinitions([
        'parent_directory' => null,
        'label' => Faker::text(20),
        'type' => DirectoryEnumType::LIST,
        'team' => 'entity|'.Team::class,
        'sort' => Faker::numberBetween(0, 20),
    ])
;
