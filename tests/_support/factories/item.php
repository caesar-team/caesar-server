<?php

use App\DBAL\Types\Enum\NodeEnumType;
use App\Entity\Directory\DirectoryItem;
use App\Entity\Directory\UserDirectory;
use App\Entity\Embedded\ItemMeta;
use App\Entity\Item;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Faker\Facade as Faker;

/* @var $fm FactoryMuffin */
$fm->define(Item::class)
//    ->setMaker(static function ($class) use ($fm) {
//        /** @var Item $item */
//        $item = new $class();
//
//        $directory = $fm->instance(UserDirectory::class);
//        $directoryItem = $fm->instance(DirectoryItem::class, [
//            'item' => $item,
//            'directory' => $directory,
//        ]);
//
//        $item->addDirectoryItem($directoryItem);
//
//        return $item;
//    })
    ->setDefinitions([
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
        'owner' => 'entity|'.User::class,
        'directory_items' => function ($item) use ($fm) {
            $directoryItem = $fm->instance(DirectoryItem::class, [
                'item' => $item,
                'directory' => 'entity|'.UserDirectory::class,
            ]);

            return new ArrayCollection([
                $directoryItem,
            ]);
        },
        'item' => null,
        'meta' => function () {
            $meta = new ItemMeta();
            $meta->setTitle(Faker::word()());

            return $meta;
        },
    ])
;
