<?php

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\Directory\AbstractDirectory;
use App\Entity\Directory\TeamDirectory;
use App\Entity\Team;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;

/* @var $fm FactoryMuffin */
$fm->define(Team::class)
    ->setMaker(static function ($class) {
        /** @var Team $team */
        $team = new $class();

        $trash = new TeamDirectory(AbstractDirectory::LABEL_TRASH);
        $trash->setType(DirectoryEnumType::TRASH);
        $trash->setTeam($team);

        $root = new TeamDirectory(AbstractDirectory::LABEL_TRASH);
        $root->setType(DirectoryEnumType::ROOT);
        $root->setTeam($team);

        $default = new TeamDirectory(AbstractDirectory::LABEL_DEFAULT);
        $default->setType(DirectoryEnumType::DEFAULT);
        $default->setTeam($team);

        $root->addChildDirectory($default);
        $default->setParentDirectory($root);

        $team->addDirectory($trash);
        $team->addDirectory($root);
        $team->addDirectory($default);

        return $team;
    })
    ->setDefinitions([
        'alias' => null,
        'title' => Faker::text(20),
    ])
;
