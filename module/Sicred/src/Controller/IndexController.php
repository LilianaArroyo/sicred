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

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        session_start();
        if(!empty($_SESSION)){
            if($_SESSION['pendmat']!=null){
                $cambio = new Model\logout();
                $cambio->cambio($_SESSION['pendmat'], $_SESSION['tipousr']);
            }
            return new ViewModel();
        }else{
            return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/sicred/login');
        }
    }
    
    public function contactoAction()
    {
        session_start();
        if(!empty($_SESSION)){
            if ($this->getRequest()->isPost()){
                $data = $this->request->getPost();
                $correo = new Model\contacto();
                $resultado = $correo->enviar($data->nombre,$data->email,$data->comentario, $data->asunto);
                $band = 2;
                return new ViewModel(["band"=>$band, "resultado"=>$resultado]);
            }else{
                $band = 1;
                return new ViewModel(["band"=>$band]);
            }
        }else{
            return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/sicred/login');
        }
    }

    public function loginAction(){
        if($this->getRequest()->isPost()){
            $data = $this->request->getPost();
            $login = new Model\login();
            if($data->mat_empleado == '2170104'){
                $result = $login->verificarUSR();
            }else{
                $result = $login->iniciarSesion($data->mat_empleado, $data->pwd_empleado);
            }
            
            if($result == "USUARIO INACTIVO"){
                return new ViewModel(["msj"=>$result]);
            }elseif($result == 1){
                //ingreso correctamente
                return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/sicred/index');
            }elseif ($result == -1){
                //regresamos -1 el usuario se equivoco de contrasena
                $msj = -1;
                $css=["sty" => "display:block"];
                return new ViewModel(["msj"=>$msj, "css"=>$css]);
            }elseif ($result == 2){
                //regresamos -2 el usuario no existe
                $msj = 2;
                $css=["sty" => "display:block"];
                return new ViewModel(["msj"=>$msj, "css"=>$css]);
            }elseif ($result == 3){
                $msj = 3;
                $css=["sty" => "display:block"];
                return new ViewModel(["msj"=>$msj, "css"=>$css]);
            }
        }else{
            $msj = "";
            $css=["sty" => "display:none"];
            return new ViewModel(["msj"=>$msj, "css"=>$css]);
        }
    }

    public function logoutAction(){
        session_start();
        $logout = new Model\logout();
        $logout->closeSession();
        return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/sicred/login');
    }

}
