<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Avatar;
use App\Services\File\FileDownloader;
use PHPUnit\Framework\TestCase;

class FileDownloaderTest extends TestCase
{
    const LINK = __DIR__.'/../../public/tests/caesar.png';
    /**
     * @var FileDownloader
     */
    private $fileDownloader;
    public function setUp(): void
    {
        $this->fileDownloader = new FileDownloader();
    }

    public function testCreateAvatarFromLink()
    {
        $link = self::LINK;
        $avatar = $this->fileDownloader->createAvatarFromLink($link);

        $this->assertInstanceOf(Avatar::class, $avatar);
        if ($avatar instanceof Avatar) {
            $avatar->removeFile();
        }
    }
}