<?php

declare(strict_types=1);

namespace App\Security\AuthorizationManager;

use App\Utils\HashidsEncoderInterface;
use Hashids\Hashids;

class InvitationEncoder implements HashidsEncoderInterface
{
    static public function initEncoder(): Hashids
    {
        return new Hashids(getenv('INVITATION_SALT'), getenv('INVITATION_HASH_LENGTH'));
    }
}