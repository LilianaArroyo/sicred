<?php
/**
 * Created by PhpStorm.
 * User: colbach
 * Date: 09/08/2017
 * Time: 8:45
 */
namespace Sicred\Model\plantel;

use Sicred\Model;

class ordenarcred{
    
    public function generarlistagrupo($grupo, $plantel){
        session_start();

        $conn = new Model\Connect();
        $db = $conn->connBD();

        $data = array();
        //FILTRO FECHA_CORTE ASC, PLANTEL ASC, GRUPO ASC, PATERNO ASC, MATERNO ASC, NOMBRE ASC
        //$sql = "SELECT ALUM.MATRICULA, ALUM.PATERNO, ALUM.MATERNO, ALUM.NOMBRE, ALUM.GRUPO, STU.PRINTING_DATE, STU.VALIDATION_DATE FROM PRIMER_INGRESO.ALUMNOS_INSCRITOS ALUM, PRIMER_INGRESO.STUDENT_REGISTERED STU WHERE ALUM.MATRICULA = STU.MATRICULA AND ALUM.PLANTEL =".$plantel." AND ALUM.GRUPO=".$grupo." AND ALUM.PERIODO='2017B' ORDER BY ALUM.FECHA_CORTE, ALUM.GRUPO, ALUM.PATERNO, ALUM.MATERNO, ALUM.NOMBRE";

        
        
        if($_SESSION['perfil'] == 1){
            $sql_db = "SELECT US.MATRICULA, US.NMBR, US.APLLD_PTRN, US.APLLD_MTRN, US.CRR_INSTTCNL, US.CT_STTS FROM USRCB.USUARIO US WHERE US.MATRICULA = MATRICULA AND US.CT_PLNTL='".$plantel."' AND GRUPO='".$grupo."'ORDER BY US.APLLD_PTRN, US.APLLD_MTRN, US.NMBR";

        }else{
           $sql_db = "SELECT US.MATRICULA, US.NMBR, US.APLLD_PTRN, US.APLLD_MTRN, US.CRR_INSTTCNL, US.CT_STTS FROM USRCB.USUARIO US WHERE US.MATRICULA = MATRICULA AND US.CT_PLNTL='".$_SESSION['plantel']."' AND GRUPO='".$grupo."'ORDER BY US.APLLD_PTRN, US.APLLD_MTRN, US.NMBR"; 
        }


        try{
            $query = oci_parse($db, $sql_db);
            oci_execute($query);
            
            while (($row = oci_fetch_row($query)) != false){
                array_push($data, [$row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6]]);
            }
            return $data;
        }catch (PDOException $e){
            return $data;
        }
    }

    public function grupos(){
        $conn = new Model\Connect();
        $db = $conn->connBD();

        $sql = "SELECT DISTINCT GRUPO FROM PRIMER_INGRESO.ALUMNOS_INSCRITOS WHERE PLANTEL=1 ORDER BY GRUPO";
        $data = array();
        
        try{
            $query = oci_parse($db, $sql);
            oci_execute($query);
            
            while($row = $query->fetch()){
                array_push($data,$row[0]);
            }
            return $data;

        }catch(PDOException $e){
            return $data;
        }
    }


}