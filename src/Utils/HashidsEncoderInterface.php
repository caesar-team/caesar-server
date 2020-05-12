<?php

namespace App\Utils;

use Hashids\Hashids;

interface HashidsEncoderInterface
{
    public static function initEncoder(): Hashids;
}
