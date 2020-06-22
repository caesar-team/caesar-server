<?php

declare(strict_types=1);

namespace App\Model\File;

use Doctrine\ORM\Mapping as ORM;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

abstract class AbstractFile
{
    public const BASE_PATH = 'static/';

    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="extension", type="string", length=5, nullable=false)
     */
    protected $extension;

    /**
     * @var File
     */
    protected $file;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    abstract protected function getSubDir(): string;

    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @throws \Exception
     *
     * @return $this
     */
    public function setFile(File $file): self
    {
        if (!$file->isReadable()) {
            throw new Exception('File read error');
        }

        $this->file = $file;
        $extension = $file->getExtension();
        if (empty($extension) || strlen($extension) > 5) {
            $extension = $file->guessExtension();
        }
        if (empty($extension)) {
            $extension = $this->generateExtension($file);
        }
        $this->setExtension($extension);

        return $this;
    }

    final public function getFilePath(): string
    {
        return sprintf('%s/%s', $this->getBasePath(), $this->getFileName());
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @return $this
     */
    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Puts file to filesystem.
     */
    final public function saveFile(?string $projectPath = null): void
    {
        $fs = new FileSystem();
        if (false === $fs->exists(strval($projectPath).$this->getBasePath())) {
            $fs->mkdir(strval($projectPath).$this->getBasePath());
        }

        $this->file->move(strval($projectPath).$this->getBasePath(), $this->getFileName());
    }

    /**
     * Removes reference file.
     */
    final public function removeFile(?string $projectPath = null): void
    {
        $fs = new FileSystem();
        $fs->remove(strval($projectPath).$this->getFilePath());
    }

    final protected function getBasePath(): string
    {
        return sprintf('%s/%s', static::BASE_PATH, $this->getSubDir());
    }

    final protected function getFileName(): string
    {
        return sprintf('%s.%s', $this->getId()->toString(), $this->getExtension());
    }

    protected function generateExtension(File $file): string
    {
        switch ($file->getMimeType()) {
            case 'text/x-Algol68':
                return 'sql';
            default:
                return 'none';
        }
    }
}
