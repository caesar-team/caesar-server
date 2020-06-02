<?php

use App\Entity\Directory;
use App\Entity\Team;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;

/** @var $fm FactoryMuffin */
$fm->define(Team::class)->setDefinitions([
    'alias' => null,
    'title' => Faker::text(20),
    'list' => 'entity|'.Directory::class,
    'trash' => 'entity|'.Directory::class,
]);
