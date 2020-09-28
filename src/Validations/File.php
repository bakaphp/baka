<?php

declare(strict_types=1);

namespace Baka\Validations;

use Baka\Validation as CanvasValidation;
use Phalcon\Http\Request\FileInterface;
use Phalcon\Validation\Validator\File as FileValidator;

class File
{
    /**
     * Validate Upload Files.
     *
     * @param FileInterface $file
     *
     * @return boolean
     */
    public static function validate(FileInterface $file) : bool
    {
        $validator = new CanvasValidation();

        /**
         * @todo add validation for other file types, but we need to
         * look for a scalable solution
         */
        $uploadConfig = [
            'maxSize' => '100M',
            'messageSize' => ':field exceeds the max filesize (:max)',
            'allowedTypes' => [
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif',
                'audio/mpeg',
                'audio/mp3',
                'text/plain',
                'audio/mpeg',
                'application/pdf',
                'audio/mpeg3',
                'audio/x-mpeg-3',
                'application/x-zip-compressed',
                'application/octet-stream',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            'messageType' => 'Allowed file types are :types',
        ];

        $validator->add(
            'file',
            new FileValidator($uploadConfig)
        );

        //phalcon has a issue it requires to be a POST to validate file, so we ignore this for now
        if (php_sapi_name() != 'cli') {
            //validate this form for password
            $validator->validate([
                'file' => [
                    'name' => $file->getName(),
                    'type' => $file->getType(),
                    'tmp_name' => $file->getTempName(),
                    'error' => $file->getError(),
                    'size' => $file->getSize(),
                ]
            ]);
        }

        return true;
    }
}
