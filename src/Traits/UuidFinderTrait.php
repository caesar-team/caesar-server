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
     * @param string $id
     *
     * @return null|object
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
