<?php
/**
 * Created by PhpStorm.
 * User: gterriere2018
 * Date: 18/02/2019
 * Time: 10:17
 */

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CsvFile
 * @package App\Entity
 */
class CsvFile
{
    /**
     * @Assert\NotBlank(message= "Veuillez choisir un fichier pour l'upload")
     */
    private $csvFileName;

    /**
     * @return mixed
     */
    public function getCsvFileName()
    {
        return $this->csvFileName;
    }

    /**
     * @param mixed $csvFileName
     */
    public function setCsvFileName($csvFileName): void
    {
        $this->csvFileName = $csvFileName;
    }
}