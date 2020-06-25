<?php

declare(strict_types=1);

namespace App\DBAL\Types\Enum;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class AccessEnumType extends AbstractEnumType
{
    public const TYPE_READ = 'read';
    public const TYPE_WRITE = 'write';

    /** @var array */
    protected static $choices = [
        self::TYPE_READ => 'enum.access_type.read',
        self::TYPE_WRITE => 'enum.access_type.write',
    ];

    public const AVAILABLE_TYPES = [
        self::TYPE_READ, self::TYPE_WRITE,
    ];
}
