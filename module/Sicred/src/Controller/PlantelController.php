<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Sicred\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Sicred\Model;

class PlantelController extends AbstractActionController
{
    public function revisioncredAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['mn_plantel'] != null){
            if($_SESSION['pendmat']!=null){
                $cambio = new Model\logout();
                $cambio->cambio($_SESSION['pendmat'], $_SESSION['tipousr']);
            }
            return new ViewModel();
        }else{
            return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/sicred/login');
        }
    }

    public function buscaralumnoAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['mn_plantel'] != null){
            if($_SESSION['pendmat']!=null){
                $cambio = new Model\logout();
                $cambio->cambio($_SESSION['pendmat'], $_SESSION['tipousr']);
            }
            if ($this->getRequest()->isPost()){
                $resultado = null;
                $data = $this->request->getPost();
                $serch = new Model\plantel\buscarAlumno();
                $resultado = $serch->getStudent($data->serch);
                if(isset($resultado)){
                    if ($resultado != -1){
                        $band = 2;
                        $height = "height:auto;";
                        return new ViewModel(["resultado"=>$resultado, "band"=>$band, "height"=>$height]);
                    }elseif($resultado == -1){
                        $band = 2;
                        $height = "height:500px;";
                        $resultado = "El alumno con la mat&iacute;cula ".$data->serch." no pertenece al Plantel ".$_SESSION['plantel'];
                        return new ViewModel(["data"=>$data, "height"=>$height, "resultado"=>$resultado, "band"=>$band]);
                    }
                }else{
                    $band = 2;
                    $height = "height:500px;";
                    $resultado = "La matr&iacute;cula <strong>".$data->serch."</strong> no fue encontrada! Por favor verifique que los datos sean correctos";
                    return new ViewModel(["data"=>$data, "height"=>$height, "resultado"=>$resultado, "band"=>$band]);
                }
            }else{
                $band = 1;
                $height = "height:500px;";

                return new ViewModel(["height"=>$height, "band"=>$band]);
            }

        }else{
            return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/sicred/login');
        }
    }

    public function gruposAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['mn_plantel'] != null){
            if($_SESSION['pendmat']!=null){
                $cambio = new Model\logout();
                $cambio->cambio();
            }
            $order = new Model\plantel\ordenarcred();
            
            if($this->request->isPost()){
                $band = 2;
                $data = $this->request->getPost();
                $lista = $order->generarlista($data->grupo);
                $css = "height: auto";
                return new ViewModel(["band" => $band,"lista"=>$lista,"data"=>$data, "css"=>$css]);
            }else{
                $band = 1;
                $css = "height: 350px";
                return new ViewModel(["band" => $band,"css"=>$css]);
            }
        }else{
            return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/sicred/login');
        }
    }
    
    public function buscaralumnonombreAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['mn_plantel'] != null){
            if($_SESSION['pendmat']!=null){
                $cambio = new Model\logout();
                $cambio->cambio($_SESSION['pendmat'], $_SESSION['tipousr']);
            }
            if ($this->getRequest()->isPost()){
                $datos = $this->request->getPost();
                $buscar = new Model\plantel\buscarAlumno();
                $resultado = $buscar->buscarAlumnoNombre($datos->nom, $datos->apat, $datos->amat);

                $band = 2;
                if($resultado != "No se pudo resolver la busqueda"){
                    //no se encontro ninguna coincidencia
                    if(count($resultado["alum"]) == 0){
                        $height = "height: 400px;";
                        return new ViewModel(["resultado"=>$resultado, "datos"=>$datos, "height"=>$height, "band"=>$band]);
                    }else{
                        $height = "height: auto";
                        return new ViewModel(["resultado"=>$resultado, "height"=>$height, "band"=>$band]);
                    }

                }else{
                    $height = "height: 500px";
                    return new ViewModel(["band" => $band,"height"=>$height,"resultado"=>$resultado]);
                }
            }else{
                $band = 1;
                $height = "height: 500px;";
                return new ViewModel(["band" => $band,"height"=>$height]);
            }

        }else{
            return $this->redirect()->toUrl('/sicred/login');
        }
    }
    
}
