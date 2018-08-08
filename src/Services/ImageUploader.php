<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 02/08/2018
 * Time: 12:17.
 */

namespace App\Services;

use App\Entity\Room;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader
{
    use TransliteratorTrait;

    /**
     * @var string
     */
    private $assetsDir;

    /**
     * ImageUploader constructor.
     *
     * @param string $assetsDir
     */
    public function __construct(string $assetsDir)
    {
        $this->assetsDir = $assetsDir;
    }

    /**
     * @param Room   $object
     * @param string $imageAttribute
     * @param string $slugAttribute
     */
    public function upload(Room $object, string $imageAttribute, string $slugAttribute): void
    {
        $imageGetter = 'get'.ucfirst($imageAttribute);
        $imageSetter = 'set'.ucfirst($imageAttribute);

        $slugGetter = 'get'.ucfirst($slugAttribute);

        if (!method_exists($object, $imageGetter) || !method_exists($object, $slugGetter)) {
            return;
        }
        /** @var UploadedFile $file */
        $file = $object->$imageGetter();
        $filename = self::slugify($object->$slugGetter()).'.'.$file->guessExtension();

        // moves the file to the directory where images are stored
        $file->move(
            $this->assetsDir,
            $filename
        );

        $object->$imageSetter($filename);
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->assetsDir;
    }
}
