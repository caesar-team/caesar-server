<?php

declare(strict_types=1);

namespace App\Model\View\Srp;

use Swagger\Annotations as SWG;

class PreparedSrpView
{
    /**
     * @SWG\Property(
     *     type="string",
     *     example="83a136ac8e5bbecb9f0d217e2431a6fbdbfa4aadd981f5aa8702edbc3430d058c6e2438f3887d7e1aa0e50595dfd7baa192e47a87c6ef606459e24a2ea01b35342d1da351dfae9ddd39c699942ee8c23ed1129307416569a1fbb4ab4fc59c0da"
     * )
     */
    private ?string $publicEphemeralValue;

    /**
     * @SWG\Property(
     *     type="string",
     *     example="d1ba20eb434a59005bf81c020ac68311dc1c6f138e760401e2a9805a62dc4272ba9e57f807086ca952d3c103d8843937af16483244743972a6954ef4c9a931af"
     * )
     */
    private ?string $seed;

    public function getPublicEphemeralValue(): ?string
    {
        return $this->publicEphemeralValue;
    }

    public function setPublicEphemeralValue(?string $publicEphemeralValue): void
    {
        $this->publicEphemeralValue = $publicEphemeralValue;
    }

    public function getSeed(): ?string
    {
        return $this->seed;
    }

    public function setSeed(?string $seed): void
    {
        $this->seed = $seed;
    }
}
