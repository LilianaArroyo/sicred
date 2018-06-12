<?php
/**
 * Created by PhpStorm.
 * User: colbach
 * Date: 26/06/2017
 * Time: 9:29
 */

namespace Sicred\Model\primer_ingreso;

use Doctrine\DBAL\Driver;

use Sicred\Model;

class buscarAlumno
{

    /*******************************  P R O C E S O - D E - P R I M E R- I N G R E S O  *******************************************************/

    public function getStudent($matricula){
        $conn = new Model\Connect();
        $db = $conn->connORACRED();

        $sql="select ALUM.MATRICULA, ALUM.NOMBRE, ALUM.PATERNO, ALUM.MATERNO, ALUM.PLANTEL, ALUM.CURP, ALUM.GRUPO, ALUM.FOLIO, TO_CHAR(STU.VALIDATION_DATE, 'DD/MM/YYYY'), STU.STATUS, ALUM.CORREO_INSTITUCIONAL,TO_CHAR(STU.PRINTING_DATE, 'DD/MM/YYYY'), STU.GROUP_ORDER FROM PRIMER_INGRESO.ALUMNOS_INSCRITOS ALUM, PRIMER_INGRESO.STUDENT_REGISTERED STU WHERE ALUM.MATRICULA = STU.MATRICULA AND ALUM.MATRICULA ='".$matricula."'";


        try{
            $query = oci_parse($db, $sql);
            oci_execute($query);
            
            $foto = new Model\FotosCurl();
            $alum = array();
            
            while(($row = oci_fetch_row($query)) != false){
                $alum = [
                    "matricula" => $row[0],
                    "nombre" => $row[1],
                    "apellidos" => $row[2]." ".$row[3],
                    "plantel" => $row[4],
                    "curp" => $row[5],
                    "grupo" => $row[6],
                    "img" => $foto->obtenerfoto($row[0]),
                    "validation" => $row[8],
                    "status" => $row[9],
                    "turno" => self::turno($row[6]),
                    "correo" => $row[10],
                    "impresion" =>$row[11],
                    "id_grupo" => $row[12] 
                ];
            }
            $query = null;
            $db = null;

            return $alum;

        }catch (Exception $e){
            return $alum;
        }
    }

    
    public function updateStudent($matricula, $perfil){
        if($matricula != ''){
            $conn = new Model\Connect();
            $db = $conn->connORACRED();

            $sqlupdate =null;
            $sql = "SELECT STATUS FROM PRIMER_INGRESO.STUDENT_REGISTERED WHERE MATRICULA='".$matricula."'";
            
            
            $query = oci_parse($db, $sql);
            oci_execute($query);
            $row = oci_fetch_row($query);

            //verificamos que la credencial es una reimpresion
            if($row[0] == 3 AND ($perfil == 'emisor')){ //que se envio una solicitud de reimpresion
                $db->beginTransaction();
                //update

                //status = 2 (reimpresa y validada)
                $sqlupdate = "UPDATE PRIMER_INGRESO.STUDENT_REGISTERED SET VALIDATION_DATE=SYSDATE, STATUS=2, MATRICULA_EMP='".$_SESSION['matricula']."' WHERE MATRICULA='".$matricula."'";
                try{
                    $update = $db->prepare($sqlupdate);
                    $update->execute();
                    $db->commit();

                }catch (Exception $e){
                    echo "Error: ".$e->getMessage();
                }

            }else{
            
                $sql_val = "SELECT VALIDATION_DATE FROM PRIMER_INGRESO.STUDENT_REGISTERED WHERE MATRICULA='".$matricula."'";
                $query = oci_parse($db, $sql_val);
                oci_execute($query);
                $row = oci_fetch_row($query);
                
                if($row[0] == null){

                    //update
                    if($perfil == 'emisor'){
                        $sqlupdate = "UPDATE PRIMER_INGRESO.STUDENT_REGISTERED SET PRINTING_DATE=SYSDATE, VALIDATION_DATE=SYSDATE, STATUS=1, MATRICULA_EMP='".$_SESSION['matricula']."' WHERE MATRICULA='".$matricula."'";
                    }elseif($perfil == 'receptor'){
                        $sqlupdate = "UPDATE PRIMER_INGRESO.STUDENT_REGISTERED SET DELIVER_DATE=SYSDATE, MATRICULA_CE='".$_SESSION['matricula']."' WHERE MATRICULA='".$matricula."'";
                    }
    
                    try{
                        $update = $db->prepare($sqlupdate);
                        $update->execute();
                        $db->commit();
    
                    }catch (Exception $e){
                        echo "Error: ".$e->getMessage();
                    }
                        
                }
            }

        }
    }

    public function enviarReimpresion($matricula){
        $conn = new Model\Connect();
        $db = $conn->connORACRED();

        //update

        //status = 3 (reimpresion)
        $sql = "UPDATE PRIMER_INGRESO.STUDENT_REGISTERED SET VALIDATION_DATE=null, STATUS=3, MATRICULA_EMP='".$_SESSION['matricula']."' WHERE MATRICULA='".$matricula."'";
        try{
            $update = oci_parse($db, $sql);
            oci_execute($update, OCI_COMMIT_ON_SUCCESS);

        }catch (Exception $e){
            echo "Error: ".$e->getMessage();
        }

    }

    private function turno($grupo){
        if($grupo<151){
            return "MATUTINO";
        }else{
            return "VESPERTINO";
        }
    }
    
}