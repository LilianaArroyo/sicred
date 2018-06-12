<?php
/**
 * Created by PhpStorm.
 * User: colbach
 * Date: 09/08/2017
 * Time: 8:45
 */
namespace Sicred\Model\reposicion;

use Sicred\Model;

class ordenarcred{

/************************************************************  P R O C E S O - D E - R E P O S I C I O N  **********************************************/

    public function peticiones($fecha, $tipo){
        $conn = new Model\Connect();
        $db = $conn->connBD();
        
        $datos = array();
        $sql_db = null;

         /*** CONSULTAS EN BDCOLBACH ******/
         if ($tipo == 0){
             $sql = "SELECT CNSCTV_ORDN, MATRICULA, NOMBRE, APLLD_PTRN, APLLD_MTRN, CVE_PLANTEL, FCH_IMPRSN, FCH_SLCTD, FCH_VLDCN, CORREO, FCH_ENTRG, DESC_ESTATUS, CT_STTS, OBSRVCNS FROM CRDNCLCB.VW_SOLICITUD_CREDENCIAL WHERE FCH_SLCTD =TO_DATE('".$fecha."', 'DD/MM/YYYY') AND (CVE_PLANTEL =21 OR CVE_PLANTEL =22 OR CVE_PLANTEL =23 OR CVE_PLANTEL =24 OR CVE_PLANTEL =25)";
         }else{
             $sql = "SELECT CNSCTV_ORDN, MATRICULA, NOMBRE, APLLD_PTRN, APLLD_MTRN, CVE_PLANTEL, FCH_IMPRSN, FCH_SLCTD, FCH_VLDCN, CORREO, FCH_ENTRG, DESC_ESTATUS, CT_STTS, OBSRVCNS FROM CRDNCLCB.VW_SOLICITUD_CREDENCIAL WHERE FCH_SLCTD =TO_DATE('".$fecha."', 'DD/MM/YYYY')";
         }

        try{
            $query = oci_parse($db, $sql);
            oci_execute($query);
            
            while(($row = oci_fetch_row($query)) != false){
                array_push($datos,$row);
            }
            
            //oci_free_statement($query);
            oci_close($db);
            
            return $datos;

        }catch(Exception $e){
            return $datos;
        }

    }

    public function planteles($fecha, $tipo){
        $conn = new Model\Connect();
        $db = $conn->connBD();

        $data = array();

        if($tipo == 0){
            $sql_db = "SELECT DISTINCT CT_PLNTL FROM CRDNCLCB.SOLICITUD_CREDENCIAL WHERE FCH_SLCTD=TO_DATE('" . $fecha . "','DD/MM/YYYY') AND (CT_PLNTL =21 OR CT_PLNTL =22 OR CT_PLNTL =23 OR CT_PLNTL =24 OR CT_PLNTL =25)ORDER BY CT_PLNTL";
        }else {
            $sql_db = "SELECT DISTINCT CT_PLNTL FROM CRDNCLCB.SOLICITUD_CREDENCIAL WHERE FCH_SLCTD=TO_DATE('" . $fecha . "','DD/MM/YYYY') ORDER BY CT_PLNTL";
        }
            try{
                $query = oci_parse($db, $sql_db);
                oci_execute($query);

                while(($row = oci_fetch_row($query)) != false){
                    array_push($data,$row[0]);
                }
                oci_free_statement($query);
                oci_close($db);
                return $data;

            }catch(Exception $e){
                return $data;
            }


    }

    public function resumenReposiciones($plantel, $mes){
        $conn = new Model\Connect();
        $db = $conn->connNUBECRED();
        // $db = $conn->connBD();
       
        $fechas = array_unique(self::fechas($mes, $plantel)); 
        $data = array();

        foreach ($fechas as $dia) {
            $fecha = $dia."/".$mes."/2017";
            $sql_db = "SELECT COUNT(*) FROM CRDNCLCB.SOLICITUD_CREDENCIAL WHERE FCH_SLCTD=TO_DATE('".$fecha."','DD/MM/YYYY') AND CT_PLNTL='".$plantel."'";
            
            $sql = "SELECT COUNT(*) FROM SIIAA_CRED_ACUM WHERE FECHA_SOLICITUD=TO_DATE('".$fecha."','DD/MM/YYYY') AND PLANTEL='".$plantel."'";
            try{
                $query = $db->query($sql);
                $row = $query->fetch();
                $aux = ["fecha"=>$fecha, "total"=>$row[0], "rev"=> "", "entr"=>""];
                array_push($data,$aux);
                
                oci_free_statement($query);
                oci_close($db);

            }catch(Exception $e){
                echo "Error: No se pudo ejecutar la sentencia SQL";
            }

        }
        
        return $data;

    }

    /** FUNCION QUE REGRESA SOLO LOS DIAS EN LA QUE FUE SOLICITADA UNA CREDENCIAL**/
    private function fechas($mes, $plantel){
        $conn = new Model\Connect();
        $db = $conn->connNUBECRED();
        // $db = $conn->connBD();
        
        $anio = 2017;
        $data = array();

        $sql_db = "SELECT EXTRACT(DAY FROM FCH_SLCTD) FROM CRDNCLCB.SOLICITUD_CREDENCIAL WHERE EXTRACT(YEAR FROM FCH_SLCTD)='".$anio."' AND EXTRACT(MONTH FROM FCH_SLCTD)='".$mes."' AND CT_PLNTL='".$plantel."' ORDER BY EXTRACT(DAY FROM FCH_SLCTD), EXTRACT(YEAR FROM FCH_SLCTD), EXTRACT(MONTH FROM FCH_SLCTD) ASC";

        try{
            $query = $db->query($sql_db);

            while($row = $query->fetch()){
                array_push($data,$row[0]);
            }
            
            oci_free_statement($query);
            oci_close($db);
            return $data;

        }catch(Exception $e){
            return $data;
        }
    }
    
     public function updateRep($fecha, $datos, $db, $opcion, $plantel){
        if (array_search('CONFIRMAR_IMPRESION', $opcion)){
            foreach ($datos as $dato){
                if ($fecha != $dato){
                    $sql = "SELECT CT_STTS FROM CRDNCLCB.SOLICITUD_CREDENCIAL WHERE FCH_SLCTD=TO_DATE('".$fecha."','DD/MM/YYYY') AND MATRICULA='".$dato."'";
                    $cons = oci_parse($db, $sql);
                    oci_execute($cons);
                    $row = oci_fetch_row($cons);
                    if($row[0]==1){
                        $sqlupdate = "UPDATE CRDNCLCB.SOLICITUD_CREDENCIAL SET FCH_IMPRSN=TO_CHAR(SYSDATE, 'DD/MM/YYYY'), CT_STTS=2 WHERE FCH_SLCTD=TO_DATE('".$fecha."','DD/MM/YYYY') AND MATRICULA='".$dato."'";
                    }else{
                        $sqlupdate = "UPDATE CRDNCLCB.SOLICITUD_CREDENCIAL SET FCH_IMPRSN=TO_CHAR(SYSDATE, 'DD/MM/YYYY') WHERE FCH_SLCTD=TO_DATE('".$fecha."','DD/MM/YYYY') AND MATRICULA='".$dato."'";
                    }
                    try{
                        $update = oci_parse($db, $sqlupdate);
                        oci_execute($update, OCI_COMMIT_ON_SUCCESS);
                        oci_free_statement($update);
                        oci_free_statement($cons);
                    }catch(PDOException $e){
                        return "";
                    }
                }
            }
            //$fch = self::fecha($fecha, $plantel,$db, $opcion);

        }elseif ($opcion[0] == 'CONFIRMAR_ENTREGA'){
            foreach ($datos as $dato){
                if ($fecha != $dato){
                    $sqlupdate = "UPDATE CRDNCLCB.SOLICITUD_CREDENCIAL SET FCH_ENTRG=TO_DATE(SYSDATE, 'DD/MM/YYYY'), CT_STTS=4 WHERE FCH_SLCTD=TO_DATE('".$fecha."','DD/MM/YYYY') AND MATRICULA='".$dato."'";
                    try{
                        $update = oci_parse($db, $sqlupdate);
                        oci_execute($update, OCI_COMMIT_ON_SUCCESS);
                        oci_free_statement($update);
                    }catch(PDOException $e){
                        return "";
                    }
                }
            }
            //$fch = self::fecha($fecha, $plantel, $db, $opcion);
        }
        //return $fch;

    }

    
    public function rechazarSol($fecha, $matricula, $obs, $db){
        $sql = "UPDATE CRDNCLCB.SOLICITUD_CREDENCIAL SET CT_STTS=5, OBSRVCNS='".$obs."', FCH_IMPRSN= null, FCH_VLDCN= null, FCH_ENTRG=null WHERE MATRICULA='".$matricula."' AND FCH_SLCTD=TO_DATE('".$fecha."','DD/MM/YYYY')";

        try{
            $update = oci_parse($db, $sql);
            oci_execute($update, OCI_COMMIT_ON_SUCCESS);
            oci_free_statement($update);

            return $matricula;
        }catch(PDOException $e){
            return "";
        }
    }

    private function emicionCred($fecha){
        // Ejemplo: 01/12/2017
        $meses = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];

        $mes = substr($fecha, 3,2);
        $year = substr($fecha, 6);

        $mes = $meses[((int) $mes-1)];

        return ["mes" =>$mes, "year"=>$year];
    }
    
    public function updateImp($fecha, $plantel){
        $conn = new Model\Connect();
        $db = $conn->connBD();

        $sqlupdate = "UPDATE CRDNCLCB.SOLICITUD_CREDENCIAL SET FCH_IMPRSN=TO_CHAR(SYSDATE, 'DD/MM/YYYY'), CT_STTS=2 WHERE FCH_SLCTD=TO_DATE('".$fecha."','DD/MM/YYYY') AND CT_PLNTL='".$plantel."'";

        try{
            $update = oci_parse($db, $sqlupdate);
            oci_execute($update, OCI_COMMIT_ON_SUCCESS);
            
            oci_free_statement($update);
            oci_close($db);
            
        }catch(PDOException $e){
            return "";
        }
    }

}