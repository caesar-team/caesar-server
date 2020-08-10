<?php

declare(strict_types=1);

namespace App\DBAL\Types\Enum;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class NodeEnumType extends AbstractEnumType
{
    public const TYPE_LIST = 'list';
    public const TYPE_INBOX = 'inbox';
    public const TYPE_TRASH = 'trash';
    public const TYPE_CRED = 'credentials';
    public const TYPE_DOCUMENT = 'document';
    public const TYPE_SYSTEM = 'system';

    /** @var array */
    protected static $choices = [
        self::TYPE_LIST => 'enum.node_type.list',
        self::TYPE_INBOX => 'enum.node_type.inbox',
        self::TYPE_TRASH => 'enum.node_type.trash',
        self::TYPE_CRED => 'enum.node_type.credentials',
        self::TYPE_SYSTEM => 'enum.node_type.system',
    ];

    public const AVAILABLE_TYPES = [
        self::TYPE_LIST, self::TYPE_INBOX, self::TYPE_TRASH, self::TYPE_CRED, self::TYPE_DOCUMENT, self::TYPE_SYSTEM,
    ];
}
