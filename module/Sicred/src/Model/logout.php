<?php
/**
 * Created by PhpStorm.
 * User: colbach
 * Date: 08/08/2017
 * Time: 12:42
 */
namespace Sicred\Model;

use Sicred\Model\reposicion;
class logout{
    public function closeSession(){
    session_start();
        if($_SESSION['pendmat']!=null){
            //conexion
            $conn = new Connect();
            $db = $conn->connBD();
            $serch = new reposicion\buscarAlumno();
            $serch->update_RepAlum($_SESSION['pendmat'],$_SESSION['tipousr'], $db);
            
            oci_close($db);
            
            //destruir todas las variables de sesion
            $_SESSION = array();
            //session_unset();
            session_destroy();
        }else{
            //destruir todas las variables de sesion
            $_SESSION = array();
            session_destroy();
        }
        
    }
    
    public function cambio($mat, $tipo){
        //conexion
        $conn = new Connect();
        $db = $conn->connBD();
        
        $serch = new reposicion\buscarAlumno();
        $serch->update_RepAlum($mat,$tipo,$db);
        
        oci_close($db);

    }


}