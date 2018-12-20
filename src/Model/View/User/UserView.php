<?php

declare(strict_types=1);

namespace App\Model\View\User;

use Swagger\Annotations as SWG;

class UserView
{
    /**
     * @var int
     *
     * @SWG\Property(example="a68833af-ab0f-4db3-acde-fccc47641b9e")
     */
    public $id;

    /**
     * @var string
     *
     * @SWG\Property(example="ipopov")
     */
    public $name;

    /**
     * @var string
     *
     * @SWG\Property(example="static/images/user/b3d4d910-bf9d-4718-b93c-553f1e6711bb.jpeg")
     */
    public $avatar;

    /**
     * @var string
     *
     * @SWG\Property(example="-----BEGIN PGP PUBLIC KEY BLOCK----- Version: OpenPGP v4.3.0 mQENBFwbZEwBCAC5BmDb4KV2pvWY5+fLV1qKotfAqpMP5utFKrytkLqoxMqsBGx4 M5rmYqddbPRSJ6k0920KSEzvicdyv3xM5ICpg6pCuh8YzFXKZNPDRfKijwOr18nz wMDwDF/7E2aBxIau3QNj9z2glg/VNo8vVXcXrq2aIIymisWgllFBVo1K39dhLHFB 25AMFbUS0UDIEQvMTK4Ed7Wmaply118vGP9T3e72tDX2eMwLST1L47h3j5U4YdNA LrI0wOoLY15+lpsOAf5FfyNpnmS3IA36K6o8KC8ns9vFa5zgYQb64H0wwY6DS8LN CN3WnrA4uW4CyR0QTjZlqOyz8JxYnxnTrhchABEBAAG0FGRzcGlyaWRvbm92QDR4 eGkuY29tiQEcBBABAgAGBQJcG2RMAAoJECWcicDF9iKz1UgH/3mCoFldkmGpFyzO KR5oCEs4520dhYConki+N/WcJu/24VFmjbdz3nab0JzrN4K5MRGKf/z10o6rfwvk ZtOpJeDu2HCWjTA79ej/cg26RNz0884sCSHyUpeGrM3kPezSLBSwy1C26DgvvbpL 3i2p/bRwYk8PwMqYfxrxC2NbjS8TqkSuNqufgBcvueIyPmb5OoF3hEzVPHXWYiGg zpNBlJy/6vS4yzRIqGqJ3zvOUO/b2GKMJY2YmiB6JFOyvViPGbIvUtHEoox+mLuT tD953zk7pJ1kkr+PZMj9k1xsOiE/8zq1SkgBQKkJTVC2ODaF52z9DOHCRyzf3bzW J/U5ZkU= =BG8s -----END PGP PUBLIC KEY BLOCK-----")
     */
    public $publicKey;

    /**
     * @var string
     *
     * @SWG\Property(example="email@gmail.com")
     */
    public $email;
}
