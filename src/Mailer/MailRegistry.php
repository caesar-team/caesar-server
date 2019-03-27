<?php

declare(strict_types=1);

namespace App\Mailer;

final class MailRegistry
{
    /**
     * Sending invite message.
     */
    public const INVITE_SEND_MESSAGE = 'invite_send_message';
    public const SHARE_SEND_MESSAGE = 'share_send_message';
    public const NEW_ITEM_MESSAGE = 'new_item_message';
    const CODES = [
        self::NEW_ITEM_MESSAGE,
    ];
}
