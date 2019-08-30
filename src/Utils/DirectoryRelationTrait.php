<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Directory;
use Doctrine\ORM\Mapping as ORM;

trait DirectoryRelationTrait
{
    /**
     * @var Directory
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Directory",
     *     cascade={"persist", "remove"}, orphanRemoval=true
     * )
     */
    protected $inbox;

    /**
     * @var Directory
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Directory",
     *     cascade={"persist", "remove"}, orphanRemoval=true
     * )
     */
    protected $lists;

    /**
     * @var Directory
     *
     * @ORM\OneToOne(
     *     targetEntity="App\Entity\Directory",
     *     cascade={"persist", "remove"}, orphanRemoval=true
     * )
     */
    protected $trash;

    /**
     * @return Directory
     */
    public function getInbox(): Directory
    {
        return $this->inbox;
    }

    /**
     * @return Directory
     */
    public function getLists(): Directory
    {
        return $this->lists;
    }

    /**
     * @return Directory
     */
    public function getTrash(): Directory
    {
        return $this->trash;
    }

}