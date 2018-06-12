<?php
/**
 * Created by PhpStorm.
 * User: colbach
 * Date: 09/08/2017
 * Time: 8:45
 */
namespace Sicred\Model\primer_ingreso;

use Sicred\Model;

class ordenarcred{


    /*********************************************  P R O C E S O - D E - P R I M E R- I N G R E S O  *******************************************************/
    public function generarlista($plantel, $grupo){
        $conn = new Model\Connect();
        $db = $conn->connORACRED();

        $data = array();
        //FILTRO FECHA_CORTE ASC, PLANTEL ASC, GRUPO ASC, PATERNO ASC, MATERNO ASC, NOMBRE ASC
        $sql = "SELECT ALUM.MATRICULA, ALUM.PATERNO, ALUM.MATERNO, ALUM.NOMBRE, ALUM.GRUPO, TO_CHAR(STU.PRINTING_DATE,'DD/MM/YYYY'), TO_CHAR(STU.VALIDATION_DATE,'DD/MM/YYYY'), STU.PROGRESIVE_NUMBER,STU.STATUS, TO_CHAR(STU.DELIVER_DATE,'DD/MM/YYYY') FROM PRIMER_INGRESO.ALUMNOS_INSCRITOS ALUM, PRIMER_INGRESO.STUDENT_REGISTERED STU WHERE ALUM.MATRICULA = STU.MATRICULA AND ALUM.PLANTEL =".$plantel." AND ALUM.GRUPO=".$grupo." AND ALUM.PERIODO='2017B' ORDER BY ALUM.FECHA_CORTE, ALUM.GRUPO, ALUM.PATERNO, ALUM.MATERNO, ALUM.NOMBRE";
        try{
            $query = oci_parse($db, $sql);
            oci_execute($query);
            
            while (($row = oci_fetch_row($query)) != false){
                array_push($data, [$row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6],$row[7],$row[8],$row[9]]);
            }

            $query = null;
            $db = null;

            return $data;
        }catch (PDOException $e){
            return $data;
        }
    }

    public function grupos(){
        self::Connection();
        $sql = "SELECT DISTINCT GRUPO FROM PRIMER_INGRESO.ALUMNOS_INSCRITOS WHERE PLANTEL=1 ORDER BY GRUPO";
        $data = array();
        try{
            $query = oci_parse($db, $sql);
            oci_execute($query);
            
            while(($row = oci_fetch_row($query)) != false){
                array_push($data,$row[0]);
            }
            return $data;

        }catch(PDOException $e){
            return $data;
        }
    }

    
    public function estaditicas($plantel){
        $conn = new Model\Connect();
        $db = $conn->connORACRED();

        $sql = "select GRUPO, TOT_CRED, TOT_VAL, TOT_ENT from primer_ingreso.resumen_credencial where periodo = '2017B' and plantel=".$plantel." order by plantel, grupo";
       // $sql = "select GRUPO, TOT_CRED, TOT_VAL, TOT_ENT, TOT_CRED - TOT_VAL AS DIFVAL, TOT_CRED - TOT_ENT AS DIFENT from primer_ingreso.resumen_credencial where periodo = '2017B' and plantel=".$plantel." order by plantel, grupo";
        $data = array();

        try{
            $query = oci_parse($db, $sql);
            oci_execute($query);
            
            while(($row = oci_fetch_row($query)) != false){
                array_push($data,$row);
            }

            return $data;

        }catch(PDOException $e){
            return $data;
        }
    }

}