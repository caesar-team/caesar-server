<?php

use App\Entity\Srp;
use App\Entity\User;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;

/**@var $fm FactoryMuffin*/
$fm->define(User::class)->setDefinitions([
    'email'  => Faker::email(),
    'username' => Faker::firstNameMale(),
    'username_canonical' => Faker::firstNameMale(),
    'email_canonical' => Faker::email(),
    'enabled' => true,
    'password' => '$2y$13$lHAN4DQVpsg.qX4SaNyDC.HXh0YFZWfj/PUTeLhXLkBR.fzjmmhWi',
    'srp' => 'entity|' . Srp::class,
    'flow_status' => User::FLOW_STATUS_FINISHED
]);