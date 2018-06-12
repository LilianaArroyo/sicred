<?php
/**
 * Created by PhpStorm.
 * User: colbach
 * Date: 26/06/2017
 * Time: 9:29
 */

namespace Sicred\Model\plantel;

use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Driver;

use Sicred\Model;


class buscarAlumno
{
    /*******************************  P L A N T E L  *******************************************************/


    public function getStudent($matricula){
        $conn = new Model\Connect();
        $db = $conn->connBD();

        //BDCOLBACH
        $sql = "SELECT US.MATRICULA, US.NOMBRE, US.PRIMER_APELLIDO, US.SEGUNDO_APELLIDO, US.CT_PLANTEL, US.CURP, CI.CRR_ELCTRNC, US.ESTATUS, US.TURNO, US.CT_MODALIDAD, US.MODALIDAD, US.SEMESTRE FROM USRCB.VW_USUARIO US, USRCB.CORREO_INSTITUCIONAL CI WHERE US.MATRICULA = CI.MATRICULA AND US.MATRICULA='".$matricula."' AND US.CT_USUARIO='4'";

        try{
            $alum = null;
            $foto = new Model\FotosCurl();
            
            $query = oci_parse($db, $sql);
            oci_execute($query);
            
            
            if(($row = oci_fetch_row($query)) != false){
                $alum = [
                    "matricula" => $row[0],
                    "img" => $foto->obtenerfoto($row[0]),
                    "nombre" => $row[1],
                    "apellidos" => $row[2]." ".$row[3],
                    "plantel" => $row[4],
                    "curp" => $row[5],
                    "correo" => $row[6],
                    "status" => $row[7],
                    "turno" => $row[8],
                    "modalidad" => $row[10],
                    "semestre" => $row[11]
                ];
            }
            if($alum != null and $_SESSION['perfil'] == 'BUSQUEDA_ALUMNOS'){
                if($alum["plantel"] != $_SESSION['plantel']){
                    return -1; //el alumno no pertenece al plantel
                }else{
                    return $alum;
                }
            }elseif($_SESSION['perfil'] == 'SUPERUSUARIO'){
                return $alum;
            }

        }catch (\Exception $e){
            return null;
        }
    }
    
    public function buscarAlumnoNombre($nom, $apat, $amat){
        $conn = new Model\Connect();
        $db = $conn->connBD();
        
        if ($nom == '' and $amat== ''){
            return "No se pudo resolver la busqueda";
        }else{
            if($_SESSION['perfil'] == 'SUPERUSUARIO'){
                if($amat != ''){
                    $sql = "SELECT US.MATRICULA, US.NOMBRE, US.PRIMER_APELLIDO, US.SEGUNDO_APELLIDO, US.CT_PLANTEL, US.CURP, CRR.CRR_ELCTRNC, US.ESTATUS, US.TURNO, US.MODALIDAD, US.SEMESTRE FROM USRCB.VW_USUARIO US, USRCB.CORREO_INSTITUCIONAL CRR WHERE US.MATRICULA=CRR.MATRICULA AND US.PRIMER_APELLIDO='".$apat."' AND US.SEGUNDO_APELLIDO='".$amat."' AND US.NOMBRE LIKE '%".$nom."%' AND US.CT_USUARIO='4' ORDER BY US.CT_PLANTEL ASC, US.MATRICULA DESC";
                }elseif ($amat ==''){
                    $sql = "SELECT US.MATRICULA, US.NOMBRE, US.PRIMER_APELLIDO, US.SEGUNDO_APELLIDO, US.CT_PLANTEL, US.CURP, CRR.CRR_ELCTRNC, US.ESTATUS, US.TURNO, US.MODALIDAD, US.SEMESTRE FROM USRCB.VW_USUARIO US, USRCB.CORREO_INSTITUCIONAL CRR WHERE US.MATRICULA=CRR.MATRICULA AND US.PRIMER_APELLIDO='".$apat."' AND US.NOMBRE LIKE '%".$nom."%' AND US.CT_USUARIO='4' ORDER BY US.CT_PLANTEL ASC, US.MATRICULA DESC";
                }elseif ($nom == ''){
                    $sql = "SELECT US.MATRICULA, US.NOMBRE, US.PRIMER_APELLIDO, US.SEGUNDO_APELLIDO, US.CT_PLANTEL, US.CURP, CRR.CRR_ELCTRNC, US.ESTATUS, US.TURNO, US.MODALIDAD, US.SEMESTRE FROM USRCB.VW_USUARIO US, USRCB.CORREO_INSTITUCIONAL CRR WHERE US.MATRICULA=CRR.MATRICULA AND US.PRIMER_APELLIDO='".$apat."' AND US.SEGUNDO_APELLIDO='".$amat."' AND US.CT_USUARIO='4' ORDER BY US.CT_PLANTEL ASC, US.MATRICULA DESC";
                }
            }else{
                if($amat != ''){
                    $sql = "SELECT US.MATRICULA, US.NOMBRE, US.PRIMER_APELLIDO, US.SEGUNDO_APELLIDO, US.CT_PLANTEL, US.CURP, CRR.CRR_ELCTRNC, US.ESTATUS, US.TURNO, US.MODALIDAD, US.SEMESTRE FROM USRCB.VW_USUARIO US, USRCB.CORREO_INSTITUCIONAL CRR WHERE US.MATRICULA=CRR.MATRICULA AND US.PRIMER_APELLIDO='".$apat."' AND US.SEGUNDO_APELLIDO='".$amat."' AND US.NOMBRE LIKE '%".$nom."%' AND US.CT_USUARIO='4' AND US.CT_PLANTEL='".$_SESSION['plantel']."' ORDER BY US.CT_PLANTEL ASC, US.MATRICULA DESC";
                }elseif ($amat ==''){
                    $sql = "SELECT US.MATRICULA, US.NOMBRE, US.PRIMER_APELLIDO, US.SEGUNDO_APELLIDO, US.CT_PLANTEL, US.CURP, CRR.CRR_ELCTRNC, US.ESTATUS, US.TURNO, US.MODALIDAD, US.SEMESTRE FROM USRCB.VW_USUARIO US, USRCB.CORREO_INSTITUCIONAL CRR WHERE US.MATRICULA=CRR.MATRICULA AND US.PRIMER_APELLIDO='".$apat."' AND US.NOMBRE LIKE '%".$nom."%' AND US.CT_USUARIO='4' AND US.CT_PLANTEL='".$_SESSION['plantel']."' ORDER BY US.CT_PLANTEL ASC, US.MATRICULA DESC";
                }elseif ($nom == ''){
                    $sql = "SELECT US.MATRICULA, US.NOMBRE, US.PRIMER_APELLIDO, US.SEGUNDO_APELLIDO, US.CT_PLANTEL, US.CURP, CRR.CRR_ELCTRNC, US.ESTATUS, US.TURNO, US.MODALIDAD, US.SEMESTRE FROM USRCB.VW_USUARIO US, USRCB.CORREO_INSTITUCIONAL CRR WHERE US.MATRICULA=CRR.MATRICULA AND US.PRIMER_APELLIDO='".$apat."' AND US.SEGUNDO_APELLIDO='".$amat."' AND US.CT_USUARIO='4' AND US.CT_PLANTEL='".$_SESSION['plantel']."' ORDER BY US.CT_PLANTEL ASC, US.MATRICULA DESC";
                }
            }
            try{
                $alum = array();
                $foto = new Model\FotosCurl();
                
                $query = oci_parse($db, $sql);
                oci_execute($query);
                
                while(($row = oci_fetch_row($query)) != false){
                    $aux = ["matricula" => $row[0],
                        "nombre" => $row[1],
                        "apellidos" => $row[2]." ".$row[3],
                        "plantel" => $row[4],
                        "curp" => $row[5],
                        "img" => $foto->obtenerfoto($row[0]),
                        "status" => $row[7],
                        "turno" => $row[8],
                        "correo" => $row[6],
                        "modalidad" => $row[9],
                        "semestre" => $row[10]
                    ];
    
                    array_push($alum, $aux);
    
                }
                return ["alum"=>$alum];
    
    
            }catch(\Exception $e){
                return null;
            }
        }
    }
    


    

}