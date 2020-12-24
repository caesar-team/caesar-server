<?php

declare(strict_types=1);

namespace App\DBAL\Types\Enum;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class DirectoryEnumType extends AbstractEnumType
{
    public const DEFAULT = 'default';
    public const ROOT = 'root';
    public const LIST = 'list';
    public const INBOX = 'inbox';
    public const TRASH = 'trash';

    /** @var array */
    protected static $choices = [
        self::DEFAULT => 'enum.directory_type.default',
        self::ROOT => 'enum.directory_type.root',
        self::LIST => 'enum.directory_type.list',
        self::INBOX => 'enum.directory_type.inbox',
        self::TRASH => 'enum.directory_type.trash',
    ];

    public const AVAILABLE_TYPES = [
        self::DEFAULT, self::ROOT, self::LIST, self::INBOX, self::TRASH,
    ];
}
