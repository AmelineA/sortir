<?php
/**
 * Created by PhpStorm.
 * User: gterriere2018
 * Date: 15/02/2019
 * Time: 14:09
 */

namespace App\Services;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FileUploader
 * @package App\Services
 */
class FileUploader
{

    /**
     * @var
     */
    private $targetDirectory;


    /**
     * FileUploader constructor.
     * @param $targetDirectory
     */
    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }



    /**
     * is used to set a unique name and its real extension to the file in parameter and then copy the file into the target directory
     * @param UploadedFile $file
     * @return string
     */
    public function upload(UploadedFile $file, String $formerFileName)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        try {
            if ($formerFileName){
                $filesystem = new Filesystem();
                $filesystem->remove($formerFileName);
            }
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // TODO... handle exception if something happens during file upload
        }

        return $fileName;
    }


    /**
     * @return mixed
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
