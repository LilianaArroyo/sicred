<?php
/**
 * Created by PhpStorm.
 * User: LanArroyo
 * Date: 18/05/2018
 * Time: 01:19 PM
 */

namespace Sicred\Model;


class generarCorreo
{
    /****  FUNCION ENCARGADA DE CREAR UNA CUENTA DE CORREO UNICA A UN USUARIO  ****/
    public function crearCorreo($apat, $amat, $nom, $db){
        //$db = $conn->connBDU();

        //nombre
        $nombre = "";
        if($apat != null){
            $nombre = $nom.".".$apat;
        }else{
            $nombre = $nom.".".$amat;
        }

        //construimos el correo personal
        //quitamos todos los espacios en blanco
        $correoPer = self::quitarEspacios($nombre);

        //quitamos acentos y eñes
        $correoPer = self::quitarCaracteresNoVal($correoPer);

        //pasamos toda la cadena a minusculas
        $correoPer = strtolower($correoPer);

        //VERIFICAMOS CUANTAS USUARIOS TIENEN UN DOMINIO PARECIDO AL CONSTRUIDO CON $correoPer
        $sqlBD = "SELECT COUNT(*) FROM USRCB.CORREO_INSTITUCIONAL WHERE CRR_ELCTRNC LIKE '".$correoPer."%'";
        try{
            $query = oci_parse($db, $sqlBD);
            oci_execute($query);
            
            $row = oci_fetch_row($query);

            if($row[0] == 0){
                $correo = $correoPer."@bachilleres.edu.mx";
                return $correo;
            }else{
                //$correo = self::generarCnsctv($correoPer, $db);
                $correo = $correoPer.$row[0]."@bachilleres.edu.mx";
                return $correo;
            }

        }catch (\Exception $e){
            return '';
        }

    }

    private function generarCnsctv($correo, $db){
        $max = 0;
        $sqlBD = "SELECT CRR_ELCTRNC FROM USRCB.CORREO_INSTITUCIONAL WHERE CRR_ELCTRNC LIKE '".$correo."%' ORDER BY CRR_ELCTRNC";

        try{
            $query = oci_parse($db, $sqlBD);
            oci_execute($query);
            
            while(($row = oci_fetch_row($query)) != false){
                $pos1 = strlen($correo);
                $pos2 = strpos($row[0], "@");
                $cnsctv = substr($row[0], $pos1, ($pos2 - $pos1));
                if ($max < $cnsctv) {
                    $max = $cnsctv;
                }
            }
            $max = $max + 1;
            $correoNuevo = $correo.$max;
            
            return $correoNuevo;

        }catch (\Exception $e){
            return "";
        }
    }

    private function quitarCaracteresNoVal($letra){
        $nombre_n = str_replace("Ñ‘", "N", $letra);
        $nombre_a = str_replace("Á", "A", $nombre_n);
        $nombre_e = str_replace("É", "E", $nombre_a);
        $nombre_i = str_replace("Í", "I", $nombre_e);
        $nombre_o = str_replace("Ó", "O", $nombre_i);
        $nombre_u = str_replace("Ú", "U", $nombre_o);

        return $nombre_u;
    }

    private function quitarEspacios($nombre){
        $cadanasinEspacios = str_replace(' ', '', $nombre);
        return $cadanasinEspacios;
    }

    private function limpiarNombre($nom, $amat, $apat){
        $nom1 = str_replace("@", "Ñ‘", $nom);
        $apat1 = str_replace("@", "Ñ‘", $apat);

        if($amat != ""){
            $amat1 = str_replace("@", "Ñ‘", $amat);
        }else
            $amat1 = "";

        return ["nombre"=>$nom1, "apat"=>$apat1, "amat"=> $amat1];
    }

}