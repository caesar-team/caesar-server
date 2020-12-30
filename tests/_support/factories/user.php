<?php

use App\DBAL\Types\Enum\DirectoryEnumType;
use App\Entity\Directory\AbstractDirectory;
use App\Entity\Directory\UserDirectory;
use App\Entity\Srp;
use App\Entity\User;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;

/* @var $fm FactoryMuffin */
$fm->define(User::class)
    ->setMaker(static function ($class) {
        /** @var User $user */
        $user = new $class();

        $inbox = new UserDirectory(AbstractDirectory::LABEL_INBOX);
        $inbox->setType(DirectoryEnumType::INBOX);
        $inbox->setUser($user);

        $trash = new UserDirectory(AbstractDirectory::LABEL_TRASH);
        $trash->setType(DirectoryEnumType::TRASH);
        $trash->setUser($user);

        $root = new UserDirectory(AbstractDirectory::LABEL_TRASH);
        $root->setType(DirectoryEnumType::ROOT);
        $root->setUser($user);

        $default = new UserDirectory(AbstractDirectory::LABEL_DEFAULT);
        $default->setType(DirectoryEnumType::DEFAULT);
        $default->setUser($user);

        $root->addChildDirectory($default);
        $default->setParentDirectory($root);

        $user->addDirectory($inbox);
        $user->addDirectory($trash);
        $user->addDirectory($root);
        $user->addDirectory($default);

        return $user;
    })
    ->setDefinitions([
        'email' => Faker::email(),
        'username' => Faker::firstNameMale(),
        'username_canonical' => Faker::firstNameMale(),
        'email_canonical' => Faker::email(),
        'enabled' => true,
        'password' => '$2y$13$lHAN4DQVpsg.qX4SaNyDC.HXh0YFZWfj/PUTeLhXLkBR.fzjmmhWi',
        'srp' => 'entity|'.Srp::class,
        'flow_status' => User::FLOW_STATUS_FINISHED,
        'roles' => [User::ROLE_USER],
    ])
;
