<?php
/**
 * Created by PhpStorm.
 * User: gterriere2018
 * Date: 15/02/2019
 * Time: 10:25
 */

namespace App\Services;


/**
 * Class ConvertCsvToArray
 * @package App\Services
 */
class ConvertCsvToArray
{

    /**
     * ConvertCsvToArray constructor.
     */
    public function __construct()
    {
    }

    /**
     * is used to convert a csv file into an array of data
     * @param $filePath
     * @param string $delimiter
     * @return array|string
     */
    public function convert($filePath, $delimiter = ',')
    {
//        dd($filePath);
        if (!file_exists($filePath)) {
            return "existe pas";
        }
        if (!is_readable($filePath)) {
            return "pas lisible";
        }

        $users = array();
        $row = 0;

        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                $num = count($data);
                $row++;
                for ($i = 0; $i < $num; $i++) {
                    $users[$row] = array(
//                        "roles"=>$data[0],
                        "name" => $data[1],
                        "first_name" => $data[2],
                        "telephone" => $data[3],
                        "email" => $data[4],
                        "activated" => $data[5],
                        "site_id" => $data[6],


                    );
                }
            }
            fclose($handle);
        }
        return $users;
    }


}