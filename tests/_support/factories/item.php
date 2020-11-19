<?php

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory;
use App\Entity\Embedded\ItemMeta;
use App\Entity\Item;
use App\Entity\User;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;

/* @var $fm FactoryMuffin */
$fm->define(Item::class)->setDefinitions([
    'parent_list' => 'entity|'.Directory::class,
    'secret' => Faker::word(),
    'raws' => Faker::word(),
    'original_item_id' => null,
    'favorite' => false,
    'type' => NodeEnumType::TYPE_CRED,
    'access' => null,
    'sort' => 0,
    'cause' => null,
    'link' => null,
    'status' => Item::STATUS_FINISHED,
    'previous_list_id' => '',
    'owner' => 'entity|'.User::class,
    'item' => null,
    'meta' => function () {
        $meta = new ItemMeta();
        $meta->setTitle(Faker::word()());

        return $meta;
    },
]);
