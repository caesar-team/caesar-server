<?php

declare(strict_types=1);

namespace App\Services\File;

use App\Entity\Avatar;
use App\Model\File\AbstractFile;
use Symfony\Component\HttpFoundation\File\File;

class FileDownloader
{
    public function createAvatarFromLink(string $link): Avatar
    {
        $avatar = new Avatar();
        $this->createFileFromLink($link, $avatar);

        return $avatar;
    }

    private function createFileFromLink(string $link, AbstractFile $file)
    {
        //TODO link validation
        $filePath = \sprintf('%s/%s', sys_get_temp_dir(), uniqid('file_', true));
        copy($link, $filePath);

        $file->setFile(new File($filePath));

        return $file;
    }
}
