<?php

namespace App\Tests\Helper;

use Doctrine\ORM\EntityManagerInterface;

class Doctrine extends \Codeception\Module
{
    public function deleteFromDatabase($object)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getModule('Doctrine2')->_getEntityManager();

        $entityManager->remove($object);
        $entityManager->flush();
    }

    public function executeQuery($query, array $params = [])
    {
        return $this->getModule('Db')->_getDriver()->executeQuery($query, $params);
    }
}
