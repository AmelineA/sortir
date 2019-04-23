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
     * @param String $formerFileName
     * @return string
     */
    public function upload(UploadedFile $file, String $formerFileName = null)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();
        try {
            if ($formerFileName !== null){
                $filesystem = new Filesystem();
                $filesystem->remove($this->getTargetDirectory().'/'.$formerFileName);
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
