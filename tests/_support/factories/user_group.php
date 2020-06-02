<?php

use App\Entity\Team;
use App\Entity\UserTeam;
use League\FactoryMuffin\FactoryMuffin;

/** @var $fm FactoryMuffin */
$fm->define(UserTeam::class)->setDefinitions([
    'group' => 'entity|'.Team::class,
    'user' => 'entity|'.User::class,
    'user_role' => UserTeam::USER_ROLE_ADMIN,
]);
