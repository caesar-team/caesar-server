<?php

namespace App\Utils;

use Hashids\Hashids;

/**
 * @deprecated
 */
interface HashidsEncoderInterface
{
    public static function initEncoder(): Hashids;
}
