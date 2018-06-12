<?php
/**
 * Created by PhpStorm.
 * User: colbach
 * Date: 03/10/2017
 * Time: 9:29
 */

namespace Sicred\Model\reposicion;


use phpoffice\phpexcel\Classes\PHPExcel\IOFactory;

use Sicred\Model;

class cargarSolicitudes
{	
	/**  LECTURA DEL EXCEL DE SIIA**/
    public function cargarInformacion($fichero, $nombre){
        chmod($fichero, 0777);
        set_time_limit(1000);
        
        $conn = new Model\Connect();
        $db = $conn->connBD();
        
        $crear = new Model\reposicion\crearExcel();
        $enaviar = new Model\contacto();
        $archCorreo = array();
   
        $datosCorreosNuevos = array();
        $datosCorreosAct = array();
        $errorCarg = array();
        $report = array();
        $correos80 = array();


        $per = $_SESSION['periodo'];


        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $objReader->setReadDataOnly(true);

        $objPHPExcel = $objReader->load($fichero);


        $cont = 0;
        $sea = -1;
        $archivo = null;
        $archivoCSV = null;
        $archivoAct = null;
        $archivoActCSV = null;
        $archResult = null;
        $archError = null;
        $mnsj_error = '';


        //VERIFICAMOS QUE SEA UN ARCHIVO VALIDO
        $encab1 = $objPHPExcel->getActiveSheet()->getCell('A1')->getValue();
        $encab2 = $objPHPExcel->getActiveSheet()->getCell('C1')->getValue();
        $encab3 = $objPHPExcel->getActiveSheet()->getCell('F1')->getValue();
        if($encab1=='ID.PTL.' and $encab2=='MATERNO' and $encab3 =='CURP'){
            //verificamos que no se haya cargado el archivo
            $fecha= $objPHPExcel->getActiveSheet()->getCell('G2')->getValue();
            $cargar = self::verificarFecha($fecha, $db);

            $pos = strpos($nombre, 'SEA');
            if ($pos === false){
                   $sea = 1;
            }else{
                $sea = 0;
            }
            if ($cargar == 0 || $sea == 0){
                //obtenemos el numero de filas del documento
                $numFilas = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
                for($i = 2; $i<=$numFilas ; $i++){
                    //$id = $objPHPExcel->getActiveSheet()->getCell('A'.$i)->getValue();
                    $paterno = $objPHPExcel->getActiveSheet()->getCell('B'.$i)->getValue();
                    $materno = $objPHPExcel->getActiveSheet()->getCell('C'.$i)->getValue();
                    $nombre = $objPHPExcel->getActiveSheet()->getCell('D'.$i)->getValue();
                    $matricula = $objPHPExcel->getActiveSheet()->getCell('E'.$i)->getValue();
                    //$curp = $objPHPExcel->getActiveSheet()->getCell('F'.$i)->getValue();
                    $fecha = $objPHPExcel->getActiveSheet()->getCell('G'.$i)->getValue();
                    $turno = $objPHPExcel->getActiveSheet()->getCell('H'.$i)->getValue();
                    //$semestre = $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getValue();
                    if($sea == 0){
                        $plntl = $objPHPExcel->getActiveSheet()->getCell('I'.$i)->getValue();
                        $correo = $objPHPExcel->getActiveSheet()->getCell('J'.$i)->getValue();
                    }else{
                        $plntl = $objPHPExcel->getActiveSheet()->getCell('J'.$i)->getValue();
                        $correo = $objPHPExcel->getActiveSheet()->getCell('K'.$i)->getValue();
                    }

                    if(self::veriUsr($matricula, $db)){
                        if ($correo == null) {
                            //verificamos en la tabla de Usuarios
                            $mail = self::verificarTabCorr($matricula, $db);
                            if($mail["correo"] == ""){
                                $correo = new Model\generarCorreo();
                                $correoNuevo = $correo->crearCorreo($paterno, $materno, $nombre, $db);
                                
                                 if (strlen($correoNuevo) <= 80){
                                    /** insert EN BD.USRCB.CRR_INSTTCNL **/
                                   $r = self::insertCorreo($correoNuevo, $matricula, $db);
                                   if($r == ""){
                                       $aux = [$correoNuevo, "sinmail@cb.com", $nombre, $paterno.' '.$materno, "ALUMNO", $matricula,$mail["turno"], "","","Ciudad de Mexico","MEXICO", "#".$matricula, $plntl, self::periodoIngreso($mail["fechaIng"]), ""];
                                       array_push($datosCorreosNuevos, $aux);
                                       //self::insertCorreo_Siiaa($matricula, $correoNuevo);
                                   }else{
                                       $aux = [$r, "sinmail@cb.com", $nombre, $paterno.' '.$materno, "ALUMNO", $matricula,$mail["turno"], "","","Ciudad de Mexico","MEXICO", "#".$matricula, $plntl, self::periodoIngreso($mail["fechaIng"]), ""];
                                       array_push($datosCorreosAct, $aux);
                                       self::insertCorreo_Siiaa($matricula, $r);
                                   }
                                }else{
                                    $aux = [$correoNuevo, "sinmail@cb.com", $nombre, $paterno.' '.$materno, "ALUMNO", $matricula,$mail["turno"], "","","Ciudad de Mexico","MEXICO", "#".$matricula, $plntl, self::periodoIngreso($mail["fechaIng"]), ""];
                                    array_push($correos80, $aux);
                                }
                                
                            }else{
                                $aux = [$mail["correo"], "sinmail@cb.com", $nombre, $paterno.' '.$materno, "ALUMNO", $matricula, $mail["turno"], " "," ","Ciudad de Mexico","MEXICO", "#".$matricula, $plntl, self::periodoIngreso($mail["fechaIng"]), " "];
                                array_push($datosCorreosAct, $aux);
                                self::insertCorreo_Siiaa($matricula, $mail["correo"]);
                            }

                        }

                        #insert BDCOLBACH
                        $seq= '';
                        if($plntl < 10){
                            $seq = '0'.$plntl;
                        }else{
                            $seq = $plntl;
                        }
                        
                        //$num = self::cnsctv_ordn($plntl, $matricula);
                        //$resul = self::insert_solicitud_credencial($matricula, $fecha, $plntl, $per, $seq,$db)) == 1
                        if(($resul = self::insert_solicitud_credencial($matricula, $fecha, $plntl, $per, $seq,$db)) == 1){
                            $num = null;
                        }else{
                            array_push($errorCarg, [$matricula, $paterno, $materno, $nombre, $plntl]);
                            break;
                        }
                        $cont++;
                    }else{
                        array_push($report, [$matricula, $nombre, $paterno, $materno, $plntl, $fecha, $correo] );
                    }

                }
                /***
                  * Verificar si el array $errorCarg hay elementos realizamos rollback
                 *  Si no hay elementos realizar commit  **/
                  if(count($errorCarg)== 0 and count($report) == 0){
                      oci_commit($db);
                      //llamar el metodo crearExcelCorreos
                      if(count($datosCorreosNuevos) > 0){
                          $archivo = $crear->excelCorreos($datosCorreosNuevos, str_replace("/", "", $fecha), 'alta');
                          $archivoCSV = $crear->crearCSV($datosCorreosNuevos, str_replace("/", "", $fecha), 'alta');
                          
                          array_push($archCorreo, $archivo);
                          array_push($archCorreo, $archivoCSV);
                      }else{
                          $archivo = "";
                          $archivoCSV = "";
                      }
          
                      if(count($datosCorreosAct) > 0){
                          $archivoAct = $crear->excelCorreos($datosCorreosAct, str_replace("/", "", $fecha), 'activar');
                          $archivoActCSV = $crear->crearCSV($datosCorreosAct, str_replace("/", "", $fecha), 'activar');

                          array_push($archCorreo, $archivoAct);
                          array_push($archCorreo, $archivoActCSV);
                      }else{
                          $archivoAct = "";
                          $archivoActCSV = "";
                      }
          
                      if ($cont > 0){
                          $result=self::resultado($fecha, $per, $db, $sea);
                          $archResult = $crear->excelResultado($result, $fecha, $cont);
                          array_push($archCorreo, $archResult);
                              
                      }else{
                          $archResult = "";
                      }
                      
                      if (count($correos80) > 0){
                          $archCorreo80 = $crear->excelCorreos($correos80, str_replace("/", "", $fecha), 'modificar');
                          array_push($archCorreo, $archCorreo80);
                      }
                      
                      
                      $archError = "";
                      $reporte = "";
                      $mnsj_error = '';
                      
                  }else{
                      oci_rollback($db);
                      
                      $archivo = "";
                      $archivoCSV = "";
                      
                      $archivoAct = "";
                      $archivoActCSV = "";
                      
                      $archResult = "";
                      $cont = 0;
                      
                      if(count($errorCarg) > 0){
                          $archError = $crear->matriculaError($errorCarg, $fecha, 'No_se_realizo_carga_alumnos_');
                          array_push($archCorreo, $archError);
                          $mnsj_error = 'No se pudo realizar la carga';
                      }else{
                          $archError = "";
                      }

                      if (count($report) > 0){
                            $reporte = $crear->matriculaError($report, "reporte".$fecha, 'Alumnos_No_Encontrados_BD_');
                            array_push($archCorreo, $reporte);
                            $mnsj_error = 'No se pudo realizar la carga';
                      }else{
                          $reporte = "";
                      }
                  }                
                

            } // fin $cargar==0
            

        }else{
            $fecha = "";
            $archivo = "Archivo no valido";
            $archivoCSV = "";
            $archivoAct = "";
            $archivoActCSV = "";
            $archResult = "";
            $archError = "";
        }

    
        if (!empty($archCorreo)){
            if (count($correos80) > 0){
                $msj = $enaviar->enviarCorreoR('Archivos generados durante el proceso de carga de solicitudes - '.$fecha, $archCorreo, "modificar");
            }else{
                $msj = $enaviar->enviarCorreoR('Archivos generados durante el proceso de carga de solicitudes - '.$fecha, $archCorreo, "normal");
            }
            
            if($msj == 'Error Enviar Mensaje'){
                oci_close($db);
                unlink($fichero);
            }else{
                oci_close($db);
                unlink($fichero);
                if ($archResult != null){
                    unlink($archResult);
                }
                if ($archivo!= null){
                    unlink($archivo);
                    unlink($archivoCSV);
                }
                if ($archivoAct!= null){
                    unlink($archivoAct);
                    unlink($archivoActCSV);
                }
                if($archError != ""){
                    unlink($archError);
                }
                if($reporte != ""){
                    unlink($reporte);
                }
            }
        }
        
        return ["registros" => $cont, "correos" => count($datosCorreosNuevos), "archivo" =>$archivo, "fecha" => $fecha, "errorCarg" => $errorCarg, "report" => $mnsj_error, "sea" => $sea];
    }

	
	private function insert_solicitud_credencial($matricula, $fecha, $plantel, $periodo, $cons, $db){
        
        $sql_insert = "INSERT INTO CRDNCLCB.SOLICITUD_CREDENCIAL(PRD_LCTV, MATRICULA, CNSCTV_ORDN, FCH_SLCTD, CT_PLNTL, CT_STTS) values('".$periodo."', '".$matricula."', CRDNCLCB.SEQ_SOLICITUD_CREDENCIAL_P".$cons.".NEXTVAL, TO_DATE('".$fecha."','DD/MM/YYYY'), '".$plantel."', 1)";

 
        //$sql_insert = "INSERT INTO CRDNCLCB.SOLICITUD_CREDENCIAL(PRD_LCTV, MATRICULA, CNSCTV_ORDN, FCH_SLCTD, CT_PLNTL, CT_STTS) values('".$periodo."', '".$matricula."', '".$cons."', TO_DATE('".$fecha."','DD/MM/YYYY'), '".$plantel."', 1)";

        try{
            $insert = oci_parse($db,$sql_insert);
            if (oci_execute($insert, OCI_NO_AUTO_COMMIT)){
                return 1;
            }else{
                return 0;
            }
            
        }catch(\Exception $e){
            return 0;
        }

    }

	/** ARCHIVO ID'S IMPRESION**/
    private function resultado($fecha, $periodo, $db, $tipo){
        $resultado = array(); //[PLANTEL, NUM_REG, ID_INI, ID_FIN]
        if ($tipo != 0){
            for ($i=1; $i<=20 ; $i++) {
                $aux = self::consultaInter($fecha, $i, $periodo, $db);

                if(!empty($aux)){
                    if(count($aux) == 1)
                        array_push($resultado, [$i,1, $aux[0], $aux[0]]);
                    else{
                        $ini = array_pop($aux);
                        $fin = $aux[0];
                        $reg = ($fin-$ini)+1;
                        array_push($resultado, [$i, $reg, $ini, $fin]);
                    }
                }
            }
        }else{
            for ($i=21; $i<=25 ; $i++) {
                $aux = self::consultaInter($fecha, $i, $periodo, $db);

                if(!empty($aux)){
                    if(count($aux) == 1)
                        array_push($resultado, [$i,1, $aux[0], $aux[0]]);
                    else{
                        $ini = array_pop($aux);
                        $fin = $aux[0];
                        $reg = ($fin-$ini)+1;
                        array_push($resultado, [$i, $reg, $ini, $fin]);
                    }
                }
            }
        }
        return $resultado;
    }

    /** funcion que regresa los intervalos de impresion de cada plantel **/
    private function consultaInter($fecha, $plantel, $periodo, $db){

        $resultado = array(); //[PLANTEL, NUM_REG, ID]
        $sql = "SELECT CNSCTV_ORDN FROM CRDNCLCB.SOLICITUD_CREDENCIAL WHERE PRD_LCTV='".$periodo."' AND FCH_SLCTD=TO_DATE('".$fecha."','DD/MM/YYYY') AND CT_PLNTL='".$plantel."' ORDER BY CNSCTV_ORDN DESC";


        try{
            $query = oci_parse($db, $sql);
            oci_execute($query);

            while(($row = oci_fetch_row($query)) != false){
                array_push($resultado,$row[0]);
            }

            oci_free_statement($query);
        }catch(\Exception $e){

        }

        return $resultado;
    }
  
    private function insertCorreo($correo, $matricula, $db){
        $band = self::verificarCorreo($matricula, $db);
        if ($band["correo"] == ""){
            $sqlBD = "INSERT INTO USRCB.CORREO_INSTITUCIONAL(MATRICULA, CRR_ELCTRNC, FCH_ALT ,CT_CRR_ELCTRNC, CT_STTS, CT_BTCR) VALUES ('".$matricula."', '".$correo."', SYSDATE,'1', '1', '1')";
            try{
                $insert = oci_parse($db,$sqlBD);
                if(oci_execute($insert)){
                    oci_free_statement($insert);
    
                    return "";
                }else{
                    return "Error Insert";
                }

            }catch(\Exception $e){
                return 0;
            }
        }else{
            return $band["correo"];
        }

    }

    private function insertCorreo_Siiaa($matricula, $usuario){
        $conn = new Model\Connect();
        $db = $conn->connORABUCB();

        $sql_veri = "SELECT usuario FROM cb_cvp_008.SIIAA_CORREOS_ALUMNOS WHERE matricula='".$matricula."'";
        try{
            $query = oci_parse($db,$sql_veri);
            oci_execute($query);
            $row = oci_fetch_row($query);
            if ($row[0] == ""){
                $sql = "insert into cb_cvp_008.SIIAA_CORREOS_ALUMNOS (matricula, usuario) values ('".$matricula."', '".$usuario."')";
                try{
                    $insert = oci_parse($db,$sql);
                    oci_execute($insert);
                    oci_free_statement($insert);

                    oci_close($db);

                    return "";

                }catch(\Exception $e){
                    return 0;
                }
            }

        }catch(\Exception $e){
            return 0;
        }
    }
    
    private function verificarFecha($fecha, $db){

   $sql = "SELECT COUNT(*) FROM CRDNCLCB.SOLICITUD_CREDENCIAL WHERE FCH_SLCTD=TO_DATE('".$fecha."','DD/MM/YYYY')";

        try{
            $query = oci_parse($db, $sql);
            oci_execute($query);
            
            $row = oci_fetch_row($query);
            return $row[0];

        }catch (\Exception $e){
            return 0;
        }

    }
    
    private function verificarTabCorr($matricula, $db){

        $sql = "SELECT CRR.CRR_ELCTRNC, TO_CHAR(US.FCH_INGRS, 'DD/MM/YYYY'), US.CT_TRN FROM USRCB.USUARIO US, USRCB.CORREO_INSTITUCIONAL CRR WHERE CRR.MATRICULA = US.MATRICULA AND US.MATRICULA='".$matricula."'";
        try{
            $query = oci_parse($db, $sql);
            oci_execute($query);
            $row = oci_fetch_row($query);

            if($row[0] == ""){
                $sql = "SELECT TO_CHAR(US.FCH_INGRS, 'DD/MM/YYYY'), US.CT_TRN FROM USRCB.USUARIO US WHERE US.MATRICULA='".$matricula."'";
                $query = oci_parse($db, $sql);
                oci_execute($query);
                $row = oci_fetch_row($query);
                return ["correo"=>"", "fechaIng"=>$row[0], "turno"=>$row[1]];
            }else{
                return ["correo"=>$row[0], "fechaIng"=>$row[1], "turno"=>$row[2]];
            }

        }catch (\Exception $e){
            return 0;
        }

    }

    private function verificarCorreo($matricula, $db){

        $sql = "SELECT CRR_ELCTRNC FROM USRCB.CORREO_INSTITUCIONAL WHERE MATRICULA='".$matricula."'";
        try{
            $query = oci_parse($db, $sql);
            oci_execute($query);
            $row = oci_fetch_row($query);

            return ["correo"=>$row[0]];

        }catch (\Exception $e){
            return 0;
        }

    }

    private function veriUsr($matricula, $db){

        $sql = "SELECT * FROM USRCB.USUARIO WHERE MATRICULA='".$matricula."'";
        try{
            $query = oci_parse($db, $sql);
            oci_execute($query);
            $row = oci_fetch_row($query);

            if($row[0] == ""){
                return ["correo"=>"", "fechaIng"=>$row[1]];;
            }else{
                return ["correo"=>$row[0], "fechaIng"=>$row[1]];
            }

        }catch (\Exception $e){
            return 0;
        }
    }
    
    private function periodoIngreso($fecha){
        $fech = str_replace("/", "", $fecha);
        $mes = substr($fech, 2,2);
        $year = substr($fech, 4);

        if (str_replace("0", "", $mes) > 6){
            $periodo = $year.'B';
        }else{
            $periodo = $year.'A';
        }

        return $periodo;
    }



    /******************************************************************/
    //cnsctv_ordn($plantel, $matricula, $periodo)
    private function cnsctv_ordn($plantel, $matricula){
        $conn = new Model\Connect();
        $db = $conn->connNUBECRED();
        $tabla = "CREDENCIAL.SIAE_CRED".$plantel."_11";

        $sql_cnsctv = "SELECT ID FROM ".$tabla." WHERE MATRICULA='".$matricula."' ORDER BY ID DESC";
        //$sql_cnsctv = "SELECT ID FROM ".$tabla." WHERE MATRICULA='".$matricula."' ORDER BY ID DESC";
        try{
            $query = oci_parse($db, $sql_cnsctv);
            oci_execute($query);
            $row = oci_fetch_row($query);
            

            return $row[0];
        }catch(\Exception $e){
            return null;
        }

    }

}