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

class PrimeringresoController extends AbstractActionController
{
    public function revisioncredAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['mn_primerIngr'] != null){
            return new ViewModel();
        }else{
            return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/sicred/login');
        }
    }

    public function buscaralumnoAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['mn_primerIngr'] != null){
            if ($this->getRequest()->isPost()){
                $resultado = null;
                $css = null;
                $data = $this->request->getPost();
                $serch = new Model\primer_ingreso\buscarAlumno();
                $resultado = $serch->getStudent($data->serch);
                if(!empty($resultado)){ 
                    //se actualizan las variables de session
                    if($data->status == 1){
                        $_SESSION['aprobmat_pi'] = $data->beforemat;
                        $_SESSION['pendmat_pi'] = $resultado["matricula"];
                        $serch->updateStudent($_SESSION['aprobmat_pi'], $_SESSION['tipousr'] );
                        $css=["sty" => "display:none"];
                    }elseif ($data->status == 2){
                        $_SESSION['aprobmat_pi'] = null;
                        $_SESSION['pendmat_pi'] = $resultado["matricula"];
                        $css=["sty" => "display:block"];
                    }
                    $band = 2;
                    $height = "height: auto;margin-top:3%";
                    return new ViewModel(["resultado"=>$resultado, "css"=>$css, "band"=>$band, "height"=>$height]);
                }else{
                    $band = 2;
                    $height = "height: 500px;margin-top:3%";
                    $resultado =["matricula"=>null,"validation"=>null];
                    $css=["sty" => "display:none"];
                    return new ViewModel(["data"=>$data, "css"=>$css, "resultado"=>$resultado, "band"=>$band, "height"=>$height]);
                }
            }else{
                $band = 1;
                $resultado =["matricula"=>null,"validation"=>null];
                $height = "height: 500px;margin-top:3%";
                $css=["sty" => "display:none"];
                return new ViewModel(["css"=>$css, "resultado"=>$resultado, "band"=>$band,"height"=>$height]);
            }

        }else{
            return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/sicred/login');
        }
    }

    public function reporterrorAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['mn_primerIngr'] != null){
            if($this->getRequest()->isPost()){
                $data = $this->request->getPost();
                $buscar = new Model\primer_ingreso\buscarAlumno();
                $buscar->enviarReimpresion($_SESSION['pendmat_pi']);
                return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/primeringreso/revisioncred');
            }else{
                return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/sicred/index');
            }

        }else{
            return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/sicred/login');
        }

    }

    public function listasAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['mn_primerIngr'] != null){
            $order = new Model\primer_ingreso\ordenarcred();
            if($this->request->isPost()){
                $band = 2;
                $css = "margin-top:3%;height: auto";
                $data = $this->request->getPost();
                $lista = $order->generarlista($data->plantel,$data->grupo);
                return new ViewModel(["band" => $band,"lista"=>$lista,"data"=>$data, "css"=>$css]);
            }else{
                $band = 1;
                $css = "margin-top:5%;height: 500px";
                return new ViewModel(["band" => $band, "css"=>$css]);
            }
        }else{
            return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/sicred/login');
        }
    }

    public function resumenAction(){
        session_start();
        if(!empty($_SESSION) and $_SESSION['mn_primerIngr'] != null){
            $order = new Model\primer_ingreso\ordenarcred();
            if($this->request->isPost()){
                $data = $this->request->getPost();
                $band = 2;
                $css = "margin-top:3%;height: auto";
                $lista = $order->estaditicas($data->plantel);
                return new ViewModel(["band" => $band,"lista"=>$lista,"data"=>$data, "css"=>$css]);
            }else{
                $band = 1;
                $css = "margin-top:5%;height: 500px";
                return new ViewModel(["band" => $band, "css"=>$css]);
            }
        }else{
            return $this->redirect()->toUrl($this->getRequest()->getBaseURL().'/sicred/login');
        }

    }
}