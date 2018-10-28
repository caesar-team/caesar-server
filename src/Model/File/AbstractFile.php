<?php

declare(strict_types=1);

namespace App\Model\File;

use Doctrine\ORM\Mapping as ORM;
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

    /**
     * @return null|File
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @param File $file
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setFile(File $file)
    {
        if (!$file->isReadable()) {
            throw new \Exception('File read error');
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

    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    /**
     * @return string
     */
    final public function getFilePath(): string
    {
        return sprintf('%s/%s', $this->getBasePath(), $this->getFileName());
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     *
     * @return $this
     */
    public function setExtension(string $extension)
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * Puts file to filesystem.
     *
     * @param null|string $projectPath
     */
    final public function saveFile(?string $projectPath = null)
    {
        $fs = new FileSystem();
        if (false === $fs->exists($projectPath.$this->getBasePath())) {
            $fs->mkdir($projectPath.$this->getBasePath());
        }

        $this->file->move($projectPath.$this->getBasePath(), $this->getFileName());
    }

    /**
     * Removes reference file.
     *
     * @param null|string $projectPath
     */
    final public function removeFile(?string $projectPath = null)
    {
        $fs = new FileSystem();
        $fs->remove($projectPath.$this->getFilePath());
    }

    final protected function getBasePath(): string
    {
        return sprintf('%s/%s', static::BASE_PATH, $this->getSubDir());
    }

    final protected function getFileName(): string
    {
        return sprintf('%s.%s', $this->getId(), $this->getExtension());
    }

    abstract protected function getSubDir(): string;

    /**
     * @param File $file
     *
     * @return string
     */
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
