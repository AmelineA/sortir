<?php
/**
 * Created by PhpStorm.
 * User: gterriere2018
 * Date: 15/02/2019
 * Time: 10:25
 */

namespace App\Services;


class ConvertCsvToArray
{

    public function __construct()
    {
    }

    public function convert($filename, $delimiter = ',')
    {
        if(!file_exists($filename) || !is_readable($filename)) {
            return FALSE;
        }

        $users=array();
        $row = 0;

        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                $num=count($data);
                $row++;
                for ($i=0; $i<$num; $i++){
                    $users[$row]= array(
                        "username"=>$data[0],
                        "roles"=>$data[1],
                        "password"=>$data[2],
                        "name"=>$data[3],
                        "first_name"=>$data[4],
                        "telephone"=>$data[5],
                        "email"=>$data[6],
                        "activated"=>$data[7],
                        "site_id"=>$data[8],


                    );
                }
            }
            fclose($handle);
        }
        return $users;
    }



}