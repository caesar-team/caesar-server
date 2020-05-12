<?php

declare(strict_types=1);

namespace App\Traits;

use Ramsey\Uuid\Uuid;

/**
 * Trait UuidFinderTrait.
 */
trait UuidFinderTrait
{
    /**
     * @return object|null
     */
    public function findByUuid(string $id)
    {
        $entity = null;

        if (Uuid::isValid($id)) {
            $entity = $this->find($id);
        }

        return $entity;
    }
}
