<?php

declare(strict_types=1);

namespace App\Mailer;

final class MailRegistry
{
    /**
     * Sending invite message.
     */
    public const INVITE_SEND_MESSAGE = 'invite_send_message';
    public const NEW_ITEM_MESSAGE = 'new_item_message';
    public const NEW_TEAM_MEMBER_MESSAGE = 'new_team_member_message';
    public const UPDATED_ITEM_MESSAGE = 'updated_item_message';
    public const NEW_ITEMS_AND_UPDATES_MESSAGE = 'new_items_and_updates_message';

    public const MESSAGE_TEMPLATES = [
        self::INVITE_SEND_MESSAGE,
        self::NEW_ITEM_MESSAGE,
        self::NEW_TEAM_MEMBER_MESSAGE,
        self::UPDATED_ITEM_MESSAGE,
        self::NEW_ITEMS_AND_UPDATES_MESSAGE,
    ];
}
