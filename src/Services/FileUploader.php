<?php
/**
 * Created by PhpStorm.
 * User: gterriere2018
 * Date: 15/02/2019
 * Time: 14:09
 */

namespace App\Services;


use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{

    private $targetDirectory;


    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }


    //Donne un nom et son extension réelle au fichier reçu en paramètre puis copie le fichier dans le targetDirectory
    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $fileName;
    }


    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
