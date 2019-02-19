<?php
/**
 * Created by PhpStorm.
 * User: gterriere2018
 * Date: 18/02/2019
 * Time: 10:17
 */

namespace App\Entity;


class CsvFile
{
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