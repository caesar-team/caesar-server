<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PublicKeyCredentialSource;
use Webauthn\Bundle\Repository\PublicKeyCredentialSourceRepository as BasePublicKeyCredentialSourceRepository;

use Doctrine\Common\Persistence\ManagerRegistry;

final class PublicKeyCredentialSourceRepository extends BasePublicKeyCredentialSourceRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicKeyCredentialSource::class);
    }
}