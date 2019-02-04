<?php

declare(strict_types=1);

namespace App\Event\EntityListener;

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
        $this->projectDir = \sprintf('%s/%s/', $projectDir, self::PUBLIC_FOLDER);
    }

    /**
     * @PostPersist
     *
     * @param AbstractFile $file
     */
    public function postPersistHandler(AbstractFile $file)
    {
        $file->saveFile($this->projectDir);
    }

    /**
     * @PreRemove
     *
     * @param AbstractFile $file
     */
    public function preRemoveHandler(AbstractFile $file)
    {
        $file->removeFile($this->projectDir);
    }
}
