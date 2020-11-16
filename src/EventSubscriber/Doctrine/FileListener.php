<?php

declare(strict_types=1);

namespace App\EventSubscriber\Doctrine;

use App\Model\File\AbstractFile;
use Doctrine\ORM\Mapping\PostPersist;
use Doctrine\ORM\Mapping\PreRemove;

class FileListener
{
    public const PUBLIC_FOLDER = 'public';

    /** @var string */
    protected $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = sprintf('%s/%s/', $projectDir, self::PUBLIC_FOLDER);
    }

    /**
     * @PostPersist
     */
    public function postPersistHandler(AbstractFile $file)
    {
        $file->saveFile($this->projectDir);
    }

    /**
     * @PreRemove
     */
    public function preRemoveHandler(AbstractFile $file)
    {
        $file->removeFile($this->projectDir);
    }
}
