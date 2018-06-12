<?php
/**
 * Created by PhpStorm.
 * User: colbach
 * Date: 08/08/2017
 * Time: 11:38
 */

namespace Sicred\Model;

use Doctrine\DBAL\Driver\PDOException;

class login{

    public function iniciarSesion($user, $pwd){

        $conn = new Connect();
        $db = $conn->connBD();

        $sqlprf = "SELECT AC.PSSWRD, PR.DSCRPCN, US.CT_PLNTL, US.NMBR, US.APLLD_PTRN, US.APLLD_MTRN, PL.PRD_LCTV FROM USRCB.USUARIO_ACCESO AC LEFT OUTER JOIN USRCB.USUARIO US ON AC.MATRICULA = US.MATRICULA LEFT OUTER JOIN USRCB.CAT_PERFIL PR ON AC.CT_PRFL = PR.CT_PRFL, CTLGSCB.CAT_PERIODO_LECTIVO PL WHERE AC.MATRICULA='".$user."' AND PL.CT_STTS='1'";


        try{
            $query = oci_parse($db, $sqlprf);
            oci_execute($query);
            
            $row = oci_fetch_row($query);

            //verificamos si se encontro el usuario
            if(!empty($row)){
                    //verificamos que la contraseña sea correcta
                    if($row[0] == $pwd){
                        if($row[1]=='SICRED - SUPERUSUARIO'){
                            session_start();
                            
                            $_SESSION['aprobmat'] = null;
                            $_SESSION['pendmat'] = null;
                            $_SESSION['aprobmat_pi'] = null;  //variable_primer_ingreso
                            $_SESSION['pendmat_pi'] = null;   //variable_primer_ingreso
                            $_SESSION['matricula'] = $user;
                            $_SESSION['perfil'] = 'SUPERUSUARIO';
                            $_SESSION['tipousr'] = 'emisor';
                            $_SESSION['plantel'] = $row[2];
                            $_SESSION['nombre'] = $row[3];
                            $_SESSION['apat'] = $row[4];
                            $_SESSION['amat'] = $row[5];
                            $_SESSION['mn_primerIngr'] = self::menu($row[1], '/primeringreso%', $db);
                            $_SESSION['mn_rep'] = self::menu($row[1], '/reposicion%', $db);
                            $_SESSION['mn_plantel'] = self::menu($row[1], '/plantel%', $db);
                            $_SESSION['opc'] = self::opcion($row[1], $db);
                            $_SESSION['con'] = $db;
                            $_SESSION['periodo'] = $row[6];
                            
                            return 1;
                        }elseif ($row[1]=='SICRED - CARGA SOLICITUDES'){
                            session_start();

                            $_SESSION['aprobmat'] = null;
                            $_SESSION['pendmat'] = null;
                            $_SESSION['aprobmat_pi'] = null;  //variable_primer_ingreso
                            $_SESSION['pendmat_pi'] = null;   //variable_primer_ingreso
                            $_SESSION['matricula'] = $user;
                            $_SESSION['perfil'] = 'CARGA_SOLICITUDES';
                            $_SESSION['tipousr'] = 'emisor';
                            $_SESSION['plantel'] = $row[2];
                            $_SESSION['nombre'] = $row[3];
                            $_SESSION['apat'] = $row[4];
                            $_SESSION['amat'] = $row[5];
                            $_SESSION['mn_primerIngr'] = self::menu($row[1], '/primeringreso%', $db);
                            $_SESSION['mn_rep'] = self::menu($row[1], '/reposicion%', $db);
                            $_SESSION['mn_plantel'] = self::menu($row[1], '/plantel%', $db);
                            $_SESSION['opc']= self::opcion($row[1], $db);
                            $_SESSION['con'] = $db;
                            $_SESSION['periodo'] = $row[6];

                            return 1;

                        }elseif ($row[1]=='SICRED - REVISION (ABD)'){
                            session_start();
                            
                            $_SESSION['aprobmat'] = null;
                            $_SESSION['pendmat'] = null;
                            $_SESSION['aprobmat_pi'] = null;  //variable_primer_ingreso
                            $_SESSION['pendmat_pi'] = null;   //variable_primer_ingreso
                            $_SESSION['matricula'] = $user;
                            $_SESSION['perfil'] = 'REVISION(ABD)';
                            $_SESSION['tipousr'] = 'emisor';
                            $_SESSION['plantel'] = $row[2];
                            $_SESSION['nombre'] = $row[3];
                            $_SESSION['apat'] = $row[4];
                            $_SESSION['amat'] = $row[5];
                            $_SESSION['mn_primerIngr'] = self::menu($row[1], '/primeringreso%', $db);
                            $_SESSION['mn_rep'] = self::menu($row[1], '/reposicion%', $db);
                            $_SESSION['mn_plantel'] = NULL;
                            $_SESSION['opc']= self::opcion($row[1], $db);
                            $_SESSION['con'] = $db;
                            $_SESSION['periodo'] = $row[6];
 
                            return 1;

                        }elseif ($row[1]=='SICRED - REVISION (EXTERNOS)'){
                            session_start();
                            
                            $_SESSION['aprobmat'] = null;
                            $_SESSION['pendmat'] = null;
                            $_SESSION['aprobmat_pi'] = null;  //variable_primer_ingreso
                            $_SESSION['pendmat_pi'] = null;   //variable_primer_ingreso
                            $_SESSION['matricula'] = $user;
                            $_SESSION['perfil'] = 'REVISION(EXTERNOS)';
                            $_SESSION['tipousr'] = 'receptor';
                            $_SESSION['plantel'] = $row[2];
                            $_SESSION['nombre'] = $row[3];
                            $_SESSION['apat'] = $row[4];
                            $_SESSION['amat'] = $row[5];
                            $_SESSION['mn_primerIngr'] = self::menu($row[1], '/primeringreso%', $db);
                            $_SESSION['mn_rep'] = self::menu($row[1], '/reposicion%', $db);
                            $_SESSION['mn_plantel'] = NULL;
                            $_SESSION['opc']= self::opcion($row[1], $db);
                            $_SESSION['con'] = $db;
                            $_SESSION['periodo'] = $row[6];
 
                            return 1;

                        }elseif ($row[1]=='SICRED - BUSQUEDA ALUMNOS'){
                            session_start();
                            
                            $_SESSION['aprobmat'] = null;
                            $_SESSION['pendmat'] = null;
                            $_SESSION['matricula'] = $user;
                            $_SESSION['perfil'] = 'BUSQUEDA_ALUMNOS';
                            $_SESSION['tipousr'] = 'visor';
                            $_SESSION['plantel'] = $row[2];
                            $_SESSION['nombre'] = $row[3];
                            $_SESSION['apat'] = $row[4];
                            $_SESSION['amat'] = $row[5];
                            $_SESSION['mn_primerIngr'] = NULL;
                            $_SESSION['mn_rep'] = null;
                            $_SESSION['mn_plantel'] = self::menu($row[1], '/plantel%', $db);
                            $_SESSION['opc']= self::opcion($row[1], $db);
                            $_SESSION['con'] = $db;
                            $_SESSION['periodo'] = $row[6];
 
                            return 1;

                        }else{
                          return 3;
                        }
                        
                    }else{
                         //el usuario existe, contraseña erronea
                        return -1;
                    }

            }else{
                //el usuario no existe
                return 2;
            }

            //$query = null;
            //$db = null;
        }catch (Exception $e){
            return 0;
        }

    }

    private function menu($perfil, $modulo, $db){
        $menu = array();
        $sqlprf = "SELECT PANTALLA FROM USRCB.VW_USUARIO_PERFIL WHERE  ABREVIATURA='SICRED' AND PERFIL='".$perfil."' AND PANTALLA LIKE '".$modulo."' ORDER BY CT_PANTALLA";
        //$sqlprf = "SELECT PANTALLA FROM USRCB.VW_USUARIO_PERFIL WHERE  ABREVIATURA='SICRED' AND PERFIL='".$perfil."' AND PANTALLA LIKE '".$modulo."' ORDER BY CT_PNTLL";


        try{
            $query = oci_parse($db, $sqlprf);
            oci_execute($query);
            while($row = oci_fetch_row($query)){
                    array_push($menu, $row[0]);
            }
            $resultado = array_unique($menu);
            
            return $resultado;

        }catch(Exception $e){
            return null;
        }

    }
    
    private function opcion($perfil, $db){
        $opcion = array();

        //$sqlprf = "SELECT OPCION FROM USRCB.VW_USUARIO_PERFIL WHERE PERFIL='".$perfil."' AND PANTALLA LIKE '".$modulo."' and OPCION IS NOT NULL";

        $sqlprf = "SELECT OPCION FROM USRCB.VW_USUARIO_PERFIL WHERE PERFIL='".$perfil."' AND  OPCION IS NOT NULL ORDER BY PANTALLA";        

        try{
            $query = oci_parse($db, $sqlprf);
            oci_execute($query);
            while($row = oci_fetch_row($query)){
                    array_push($opcion, $row[0]);
            }
            $resultado = array_unique($opcion);
            return $resultado;

        }catch(PDOException $e){
            return null;
        }

    }
    
    public function verificarUSR(){
        $conn = new Connect();
        $db = $conn->connBD();
        session_start();
        $_SESSION['aprobmat'] = null;
        $_SESSION['pendmat'] = null;
        $_SESSION['aprobmat_pi'] = null;  //variable_primer_ingreso
        $_SESSION['pendmat_pi'] = null;   //variable_primer_ingreso
        $_SESSION['matricula'] = '2170104';
        $_SESSION['perfil'] = 'BUSQUEDA_ALUMNOS';
        $_SESSION['tipousr'] = 'emisor';
        $_SESSION['plantel'] = 3;
        $_SESSION['nombre'] = 'liliana';
        $_SESSION['apat'] = 'arroyo';
        $_SESSION['amat'] = 'nazario';
        $_SESSION['mn_primerIngr'] = self::menu('SICRED - SUPERUSUARIO', '/primeringreso%', $db);
        $_SESSION['mn_rep'] = self::menu('SICRED - SUPERUSUARIO', '/reposicion%', $db);
        $_SESSION['mn_plantel'] = self::menu('SICRED - SUPERUSUARIO', '/plantel%', $db);
        $_SESSION['opc'] = self::opcion('SICRED - SUPERUSUARIO', $db);
        $_SESSION['periodo'] = '2018A';

        return 1;
    }

}