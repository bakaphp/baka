<?php

declare(strict_types=1);

namespace Baka\Filesystem;

use Baka\Contracts\FileSystem\FileSystemInterface;
use Baka\Exception\FileSystemException;
use Phalcon\Http\Request\File;
use Phalcon\Image\Adapter\Gd;

class Helper
{
    /**
     * Generate a unique name in a specific dir.
     *
     * @param string $dir the specific dir where the file will be saved
     * @param bool $withPath
     *
     * @return string
     */
    public static function generateUniqueName(File $file, string $dir, $withPath = false) : string
    {
        // the provided path has to be a dir
        if (!is_dir($dir)) {
            throw new FileSystemException("The dir provided: '{$dir}' isn't a valid one.");
        }

        $path = tempnam($dir . '/', '');

        //this function creates a file (like touch) so, we have to delete it.
        unlink($path);
        $uniqueName = $path;
        if (!$withPath) {
            $uniqueName = str_replace($dir, '', $path);
        }

        return $uniqueName . '.' . strtolower($file->getExtension());
    }

    /**
     * Create a File instance from a given path.
     *
     * @param string $path Path of the file to be used
     *
     * @return File
     */
    public static function pathToFile(string $path) : File
    {
        //Simulate the body of a Phalcon\Request\File class
        return new File([
            'name' => basename($path),
            'type' => mime_content_type($path),
            'tmp_name' => $path,
            'error' => 0,
            'size' => filesize($path),
        ]);
    }

    /**
     * Is this file a image?
     *
     * @param File $file
     *
     * @return boolean
     */
    public static function isImage(File $file) : bool
    {
        return strpos(mime_content_type($file->getTempName()), 'image/') === 0;
    }

    /**
     * Given a image set its dimension.
     *
     * @param File $file
     * @param FileSystem $fileSystem
     *
     * @return void
     */
    public static function setImageDimensions(File $file, FileSystemInterface $fileSystem) : void
    {
        if (Helper::isImage($file)) {
            $image = new Gd($file->getTempName());
            $fileSystem->set('width', $image->getWidth());
            $fileSystem->set('height', $image->getHeight());
            $fileSystem->set(
                'orientation',
                $image->getHeight() > $image->getWidth() ? 'portrait' : 'landscape'
            );
        }
    }
}
