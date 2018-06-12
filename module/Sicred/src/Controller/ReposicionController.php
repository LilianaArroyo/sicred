<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Sicred\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Sicred\Model;

class ReposicionController extends AbstractActionController
{
    public function revisioncredAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['mn_rep'] != null){
            if($_SESSION['pendmat']!=null){
                 $cambio = new Model\logout();
                 $cambio->cambio($_SESSION['pendmat'], $_SESSION['tipousr']);
                    }
            return new ViewModel();
        }else{
            return $this->redirect()->toUrl('/sicred/login');
        }
    }

    public function cargarsolicitudAction(){
    	session_start();
        if(!empty($_SESSION) and ($_SESSION['perfil'] == 'SUPERUSUARIO' || $_SESSION['perfil'] == 'CARGA_SOLICITUDES')){
            if($_SESSION['pendmat']!=null){
                $cambio = new Model\logout();
                $cambio->cambio($_SESSION['pendmat'], $_SESSION['tipousr']);
            }
        	if ($this->getRequest()->isPost()){
        		if(!empty($_FILES["solicitudes"])){
        			$dir_subida = $_SERVER['DOCUMENT_ROOT'].'/ArchivosExcel';
        			$archivo = basename($_FILES['solicitudes']['name']);
        			$fichero_subido = $dir_subida.'/'.$archivo;
        			if(move_uploaded_file($_FILES['solicitudes']['tmp_name'], $fichero_subido)){
        				//cargar informacion
        				$cargarSolicitudes = new Model\reposicion\cargarSolicitudes();
        				$resultado = $cargarSolicitudes->cargarInformacion($fichero_subido, $archivo);

        				$band = 2;
        				if ($resultado["report"] == 'No se pudo realizar la carga') {
                            $msj = "No se pudo realizar la carga";
                            $css = "height: 500px;margin-top:5%;";
                            return (["resultado" => $resultado, "band" => $band, "msj" => $msj, "css"=>$css]);

                        }elseif ($resultado["registros"] == 0 and $resultado["archivo"]==""){
                            $msj ="";
                            $css = "height: 500px;margin-top:5%;";
                            return new ViewModel(["resultado" => $resultado, "band" => $band, "msj" => $msj, "css"=>$css]);
                        }elseif ($resultado["registros"] == 0 and $resultado["archivo"]=="Archivo no valido"){
                            $msj ="";
                            $css = "height: 500px;margin-top:5%;";
                            return new ViewModel(["resultado" => $resultado, "band" => $band, "msj" => $msj, "css"=>$css]);
                        }else{
                            $msj ="solicitudes cargadas";
                            $css = "height: auto;margin-top:3%;";
                            $order = new Model\reposicion\ordenarcred();
                            $plantel = $order->planteles($resultado["fecha"], $resultado["sea"]);
                            $lista = $order->peticiones($resultado["fecha"], $resultado["sea"]);
                            return new ViewModel(["resultado" => $resultado, "band" => $band, "msj" => $msj, "lista" => $lista, "plantel" => $plantel, "css"=>$css]);
                        }
        			}
        		}
        	}else{
        		$band = 1;
                $msj ="";
                $css = "height: 500px;margin-top:5%;";
                return new ViewModel(["band" => $band, "msj"=>$msj, "css"=>$css]);
        	}
        }else{
            return $this->redirect()->toUrl('/sicred/login');
        }
    }

    public function buscaralumnoAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['mn_rep'] != null){
            if ($this->getRequest()->isPost()){
                //conexion
                $conn = new Model\Connect();
                $db = $conn->connBD();
            
                $resultado = null;
                $css = null;
                $band =2;
                $data = $this->request->getPost();
                $buscar = new Model\reposicion\buscarAlumno();
                $resultado = $buscar->getStudentRep($data->serch, $db);
                $inter = $buscar->intervalos($resultado["fecha"],$resultado["plantel"], $db);
                if(isset($resultado)){
                    //se actualizan las variables de session
                    if($data->status == 1){
                          $_SESSION['aprobmat'] = $data->beforemat;
                          $_SESSION['pendmat'] = $resultado["matricula"];
                          $buscar->update_RepAlum($_SESSION['aprobmat'], $_SESSION['tipousr'], $db);
                          $css=["sty" => "display:none", "stts"=> 1];
                    }elseif ($data->status == 2){
                          $_SESSION['aprobmat'] = null;
                          $_SESSION['pendmat'] = NULL;
                          $css=["sty" => "display:block", "stts"=> 2];
                    }

                    $height = "height: auto;margin-top:1.5%";
                    oci_close($db);
                    return new ViewModel(["resultado"=>$resultado, "css"=>$css, "band"=>$band, "height"=>$height, "inter"=>$inter]);
                }else{
                    $height = "height: 500px;margin-top:3%";
                    $resultado =["matricula"=>null, "validation"=>null, "impresion"=>"", "estatus"=>"", "fecha_solictud"=> ""];
                    $css=["sty" => "display:none", "stts"=> 1];
                    oci_close($db);
                    return new ViewModel(["data"=>$data, "css"=>$css, "resultado"=>$resultado, "band"=>$band, "height"=>$height, "inter"=>""]);
                }
                
            }else{
                if($_SESSION['pendmat']!=null){
                    $cambio = new Model\logout();
                    $cambio->cambio($_SESSION['pendmat'], $_SESSION['tipousr']);
                }
                $resultado =["matricula"=>null, "validation"=>null, "impresion"=>"","estatus"=>"", "fecha_solictud"=>""];
                $css=["sty" => "display:none", "stts"=> 1];
                $band =1;
                $height = "height: 500px;margin-top:3%";
                return new ViewModel(["css"=>$css, "resultado"=>$resultado, "height"=>$height, "band"=>$band]);
            }

        }else{
            return $this->redirect()->toUrl('/sicred/login');
        }
    }

    public function reporterrorAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['mn_rep'] != null){
            if($this->getRequest()->isPost()){
                $data = $this->request->getPost();
                $serch = new Model\reposicion\buscarAlumno();
                $serch->rechazarCredRep($data->report_mat, $data->report_obs,$data->report_fecha);
                return $this->redirect()->toUrl('/reposicion/buscaralumno');
            }else{
                return $this->redirect()->toUrl('/sicred/index');
            }

        }else{
            return $this->redirect()->toUrl('/sicred/login');
        }

    }

    public function reposicionesAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['mn_rep'] != null){
            if($_SESSION['pendmat']!=null){
                $cambio = new Model\logout();
                $cambio->cambio($_SESSION['pendmat'], $_SESSION['tipousr']);
            }
            $order = new Model\reposicion\ordenarcred();
            $request = $this->getRequest();
            $conn = new Model\Connect();
            $db = $conn->connBD();

            if ($request->isXmlHttpRequest()){
                $act = new Model\reposicion\ordenarcred();
                $f = $this->request->getPost('fecha');
                $m = $this->request->getPost('matr');
                $mot = $this->request->getPost('motivo');

                $r = $act->rechazarSol($f, $m, $mot, $db);

                $resultado = new JsonModel(array('some_parameter' => $m, 'success'=>true));
                oci_close($db);

                return $resultado;

            }elseif ($request->isPost()){

                $band = 2;
                $data = $this->request->getPost();
                $fecha = $data->date;

                $plantel = $order->planteles($fecha, -1);
                $lista = $order->peticiones($data->date, -1);

                if(count($lista) > 0){
                    $css = "height: auto;margin-top:2%;";
                    oci_close($db);

                    return new ViewModel(["band" => $band, "lista" => $lista, "fecha" => $fecha, "plantel" => $plantel, "css" => $css]);
                }else{
                    $css = "height: 500px; margin-top:4%;";
                    $archivo = "";
                    oci_close($db);
                    return new ViewModel(["band" => $band, "lista" => $lista, "fecha" => $fecha, "plantel" => $plantel, "css" => $css, "archivo"=>$archivo]);
                }
            }else{
                $band = 1;
                $css = "height: 500px; margin-top:4%;";
                $fecha = "";
                $hab = "display:none;";
                return new ViewModel(["band" => $band, "css" => $css, "fecha"=>$fecha, "hab"=>$hab]);
            }



        }else{
            return $this->redirect()->toUrl('/sicred/login');
        }
    }
    
    public function reposicionesconfAction(){
        session_start();
        $act = new Model\reposicion\ordenarcred();
        $conn = new Model\Connect();
        $db = $conn->connBD();

        if ($this->request->isPost()){
            $datos = $this->request->getPost();
            $band = 2;
            $act->updateRep($datos->fecha, $datos, $db, $_SESSION['opc'], $datos->plantel);
            $plantel = $act->planteles($datos->fecha, -1);
            $lista = $act->peticiones($datos->fecha, -1);
            $css = "height: auto;margin-top:2%;";

            oci_close($db);
            return new ViewModel(["band" => $band, "lista" => $lista, "fecha" => $datos->fecha, "plantel" => $plantel, "css" => $css]);
        }

    }

    public function descargaAction(){
        session_start();
        if(!empty($_SESSION)){
            if($this->request->isPost()){
                $data = $this->request->getPost();
                $desc = new Model\reposicion\descargaArchivo();
                $desgArch = $desc->descargaArch($data->archivo);
            }       
        }else{
            return $this->redirect()->toUrl('/sicred/login');
        }
    }

    public function controlescolarAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['perfil'] == 2 and $_SESSION['mn_rep'] != null){
            if($this->request->isPost()){
                $data = $this->request->getPost();
                $act = new Model\reposicion\ordenarcred();
                //$actualizar = $act->actualEntrega($data->date, $data->sol,$_SESSION['matricula']);

                $band = 2;
                $plantel = $act->planteles($data->date);
                $lista = $act->peticiones($data->date, "");
                $css = "height: auto;margin-top:2%;";
                $fecha = $data->date;

                $msj = "";
                for ($i=0;$i<2;$i++){
                   $msj= $msj+"<p>"+$data->sol[$i]+"</p>";
                }

                $sol = $data->sol;
                return new ViewModel(["band" => $band, "lista" => $lista, "fecha" => $fecha, "plantel" => $plantel, "css" => $css, "archivo"=>"", "archivoC"=>"", "archCorAct"=>"", "sol"=> $msj]);

            }
        }else{
            return $this->redirect()->toUrl('/sicred/login');
        }
    }
    
}