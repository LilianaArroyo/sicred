<?php
/**
 * Created by PhpStorm.
 * User: colbach
 * Date: 19/02/2018
 * Time: 10:49
 */

namespace Sicred\Model;


class FotosCurl
{
    public function obtenerfoto($matricula){
        $curl = curl_init();
        curl_setopt_array($curl,array(
            CURLOPT_URL => "https://storage.us2.oraclecloud.com/v1/Storage-a424029/fotos/".$matricula.".jpg",
            CURLOPT_RETURNTRANSFER =>true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPAUTH =>  CURLAUTH_BASIC,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_USERPWD => "sicred_fotos:5RenaSwUSpesPr0",
        ));
        $file = curl_exec($curl);
        $status = curl_getinfo($curl);

        curl_close($curl);

        if($status['http_code'] == 200){
            return $file;
        }else{
            return "";
        }
    }
}