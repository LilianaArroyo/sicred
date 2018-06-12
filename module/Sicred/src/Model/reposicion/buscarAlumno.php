<?php
/**
 * Created by PhpStorm.
 * User: colbach
 * Date: 26/06/2017
 * Time: 9:29
 */

namespace Sicred\Model\reposicion;

use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Driver;

use Sicred\Model;


class buscarAlumno
{
    protected $db;
    protected $periodoLectivo = "2017B";


/**********************************************************  P R O C E S O - D E - R E P O S I C I O N  ******************************************************************/

   public function getStudentRep($matricula, $db){
        /*$conn = new Model\Connect();
        $db = $conn->connBD();
*/
        //$sql="select MATRICULA, NOMBRE, PATERNO, MATERNO, PLANTEL, CURP, fecha_solicitud, TURNO ,CORREOINS FROM SIIAA_CRED_ACUM WHERE MATRICULA ='".$matricula."'";

        /*******  consulta a BDCOLBACH ************ **/
        $sql = "SELECT NOMBRE, APLLD_PTRN, APLLD_MTRN, MATRICULA, CURP, CORREO, MODALIDAD, TURNO, CVE_PLANTEL, DESC_ESTATUS, FCH_IMPRSN, TO_CHAR(FCH_SLCTD, 'DD/MM/YYYY'), TO_CHAR(FCH_VLDCN, 'DD/MM/YYYY'), CNSCTV_ORDN, TO_CHAR(FCH_ENTRG, 'DD/MM/YYYY'), EMISOR, RECEPTOR, CT_STTS FROM CRDNCLCB.VW_SOLICITUD_CREDENCIAL WHERE MATRICULA='".$matricula."' ORDER BY FCH_SLCTD DESC";

        try{
            $alum = null;
            $foto = new Model\FotosCurl();

            $query = oci_parse($db, $sql);
            oci_execute($query);

            if(($row = oci_fetch_row($query)) != false){
                $alum = [
                    "nombre" => $row[0],
                    "img" => $foto->obtenerfoto($row[0]),
                    "apellidos" => $row[1]." ".$row[2],
                    "matricula" => $row[3],
                    "curp" => $row[4],
                    "correo" => $row[5],
                    "modalidad" => $row[6],
                    "turno" => $row[7],
                    "plantel" => $row[8],
                    "descestatus" => $row[9],
                    "impresion" =>$row[10],
                    "fecha_solictud" => $row[11],
                    "fecha" => $row[11],
                    "validation" => $row[12],
                    "consecutivo" => $row[13], //consecutivo de ordenamiento
                    "fch_entrega"=> $row[14],
                    "emisor"=> $row[15],
                    "receptor"=> $row[16],
                    "estatus" => $row[17]
                ];
            }
            //liberamos recursos
            oci_free_statement($query);
            oci_close($db);

            return $alum;

        }catch (Exception $e){
            return $alum;
        }

    }
    
    public function intervalos($fch, $pltl, $db){
        /*$conn = new Model\Connect();
        $db = $conn->connBD();*/

        $sqlfaltan = "SELECT COUNT(*) FROM CRDNCLCB.VW_SOLICITUD_CREDENCIAL WHERE CVE_PLANTEL='".$pltl."' AND (DESC_ESTATUS = 'EN PROCESO' OR DESC_ESTATUS = 'IMPRESA') AND FCH_SLCTD=TO_DATE('".$fch."', 'DD/MM/YYYY')";
        $sqltotal = "SELECT COUNT(*) FROM CRDNCLCB.VW_SOLICITUD_CREDENCIAL WHERE CVE_PLANTEL='".$pltl."' AND FCH_SLCTD=TO_DATE('".$fch."', 'DD/MM/YYYY')";

        $query = oci_parse($db, $sqlfaltan);
        
        oci_execute($query);
        $row = oci_fetch_row($query);
        
        $query2 = oci_parse($db, $sqltotal);
        
        oci_execute($query2);
        $row2 = oci_fetch_row($query2);
        

        return ["faltan"=>$row[0], "total"=>$row2[0]];
    }

    public function update_RepAlum($matricula, $perfil, $db){
        /*$conn = new Model\Connect();
        $db = $conn->connBD();*/

        if($matricula != ''){
            $sql = "SELECT CT_STTS FROM CRDNCLCB.SOLICITUD_CREDENCIAL WHERE MATRICULA='".$matricula."'";

            $query = oci_parse($db, $sql);
            oci_execute($query);
            $row = oci_fetch_row($query);

            if($perfil == 'emisor'){
                $sqlupdate =null;
                /*****update BDCOLBACH**/
                $sqlupdate = "UPDATE CRDNCLCB.SOLICITUD_CREDENCIAL SET FCH_VLDCN=SYSDATE, MATRICULA_EMSR='".$_SESSION['matricula']."', CT_STTS=3 WHERE MATRICULA='".$matricula."'";
                try{
                    $update = oci_parse($db, $sqlupdate);
                    oci_execute($update, OCI_COMMIT_ON_SUCCESS);

                    oci_free_statement($update);

                }catch (Exception $e){
                    echo "Error: ".$e->getMessage();
                }

            }elseif ($row[0] == 3 AND $perfil == 'receptor'){
                $sqlupdate = "UPDATE CRDNCLCB.SOLICITUD_CREDENCIAL SET FCH_ENTRG=SYSDATE, MATRICULA_RCPTR='".$_SESSION['matricula']."', CT_STTS=4 WHERE MATRICULA='".$matricula."'";

                try{
                    $update = oci_parse($db, $sqlupdate);
                    oci_execute($update, OCI_COMMIT_ON_SUCCESS);

                    oci_free_statement($update);

                }catch (Exception $e){
                    echo "Error: ".$e->getMessage();
                }
            }

        }

    }

    public function rechazarCredRep($matricula,$obs, $fecha){
        $conn = new Model\Connect();
        $db = $conn->connBD();

        //reimpresion
        $sql = "UPDATE CRDNCLCB.SOLICITUD_CREDENCIAL SET CT_STTS=5, OBSRVCNS='".$obs."', FCH_IMPRSN= null, FCH_VLDCN= null, FCH_ENTRG=null, MATRICULA_EMSR='".$_SESSION['matricula']."' WHERE MATRICULA='".$matricula."' AND FCH_SLCTD=TO_DATE('".$fecha."', 'DD/MM/YYYY')";
        try{
            $update = oci_parse($db, $sql);
            oci_execute($update, OCI_COMMIT_ON_SUCCESS);
            

        }catch (PDOException $e){
            echo "Error: ".$e->getMessage();
        }

    }
    
    private function parser_fch($fecha){
        // Ejemplo: 01/12/2017
        if ($fecha != null){
            $meses = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];

            $m = substr($fecha, 3,2);

            $mes = $meses[((int) $m-1)];
            $fch =substr_replace($fecha, $mes, 3,2);
            return $fch;
        }else
            return "";
    }
    
    

}