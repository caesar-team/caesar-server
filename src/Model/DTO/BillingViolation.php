<?php

declare(strict_types=1);

namespace App\Model\DTO;

use App\Entity\Item;
use App\Entity\User;

final class BillingViolation
{
    /**
     * @var object
     */
    private $object;

    public function __construct(object $object)
    {
        $this->object = $object;
    }


    public function getObjectAlias(): string
    {
        $alias = "Unknown";
        switch(true) {
            case $this->object instanceof Item:
                $alias = "Item";
                break;
            case $this->object instanceof User:
                $alias = "User";
                break;
        }

        return $alias;
    }
}