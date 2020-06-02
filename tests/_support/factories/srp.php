<?php

use App\Entity\Srp;
use League\FactoryMuffin\FactoryMuffin;

/** @var $fm FactoryMuffin */
$fm->define(Srp::class)->setDefinitions([
    'seed' => 'e4448a3d14af7a3e211c44802ff9f1181d899eff8cd21fa83fc48cf5794caaf91d5516954372fdadfbe931f6ed85a85d36bd325b576bc52255cd03a26865fb85',
    'verifier' => '9d4550e7fab90cb40015dc44fe1fcf499ed7a5462b2fe5d4ed1f48ca8b3d4e6f7ac85789bc212983440a74028ed931f72ff088015b09723328770e72a9694e7f',
    'public_client_ephemeral_value' => 'ae7a03327be09c7ef48acb66e9e20acd8bb595f307615d74f3783a1d4247437d87f351bf17022fce5042bd0cfc94289ac6bbf1637a652b89fd6095656fd7a71e',
    'public_server_ephemeral_value' => '752630c763499b5539ba157a75e6c2081c4d1d557cd9375a0a3822cbbbee06333438cf13d0231b82964578f4a1c461fb660846390b04ddbeb8c9a80e11dcf102c96438fd5252b8c1cf45af308051875d54dc192ed493d73d9925184563817898',
    'private_server_ephemeral_value' => '88e0d5e30e61e224bb8cb78ad352892f9b1e3a776daa3463c76836fff68a91a60430c0dd28f4dba178528f588f66b1c4cc4a1f7442dc7c0aa982454e0a56d3ce',
]);
