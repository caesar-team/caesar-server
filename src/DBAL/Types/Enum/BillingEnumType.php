<?php

declare(strict_types=1);

namespace App\DBAL\Types\Enum;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

final class BillingEnumType extends AbstractEnumType
{
    public const TYPE_BASE = 'base';
    public const TYPE_EXPANDED = 'expanded';

    /** @var array */
    protected static $choices = [
        self::TYPE_BASE => 'enum.billing_type.base',
        self::TYPE_EXPANDED => 'enum.billing_type.expanded',
    ];
}