<?php

namespace App\Utils;

use Hashids\Hashids;

interface HashidsEncoderInterface
{
    static public function initEncoder(): Hashids;
}