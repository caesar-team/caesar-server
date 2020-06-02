<?php

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;

/** @var $fm FactoryMuffin */
$fm->define(Directory::class)->setDefinitions([
    'parent_list' => null,
    'label' => Faker::text(20),
    'type' => NodeEnumType::TYPE_LIST,
    'sort' => Faker::numberBetween(0, 20),
]);
