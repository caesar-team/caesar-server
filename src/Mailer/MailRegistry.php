<?php

declare(strict_types=1);

namespace App\Mailer;

final class MailRegistry
{
    /**
     * Sending invite message.
     */
    public const INVITE_SEND_MESSAGE = 'invite_send_message';
    /**
     * Sending share items message.
     */
    public const SHARE_ITEM = 'share_item';
    /**
     * Sending update items message.
     */
    public const UPDATE_ITEM = 'update_item';
    /**
     * Sending share and update items message.
     */
    public const SHARE_AND_UPDATE_ITEM = 'share_and_update_item';
    /**
     * Sending after add user to team.
     */
    public const ADD_TO_TEAM = 'add_to_team';
    /**
     * Sending to admin after new registration on domain.
     */
    public const NEW_REGISTRATION = 'new_registration';

    public const SHARE_SEND_MESSAGE = 'share_send_message';
    public const NEW_ITEM_MESSAGE = 'new_item_message';
}
