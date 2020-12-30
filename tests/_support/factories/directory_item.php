<?php

use App\Entity\Directory\DirectoryItem;
use App\Entity\Directory\UserDirectory;
use App\Entity\Item;
use League\FactoryMuffin\FactoryMuffin;

/* @var $fm FactoryMuffin */
$fm->define(DirectoryItem::class)
    ->setDefinitions([
        'item' => 'entity|'.Item::class,
        'directory' => 'entity|'.UserDirectory::class,
    ])
;
