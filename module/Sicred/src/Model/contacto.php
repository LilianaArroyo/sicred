<?php
/**
 * Created by PhpStorm.
 * User: colbach
 * Date: 31/01/2018
 * Time: 12:06
 */
namespace Sicred\Model;


use PHPMailer\PHPMailer\PHPMailer;

require '/proyecto/sicred/vendor/autoload.php';


class contacto{

    public function enviar($nombreusr, $correousr, $cuerpo, $asunto){
        $enviadoPor = 'Enviado por: '.$nombreusr.'<br/>Correo Electronico: '.$correousr;

        $mail = new PHPMailer(false);
        $mail->isSMTP();
        $mail->SMTPDebug = 0; //SMTPDebug=1 solo despliega errores, con SMTPDebug=2 despliega todo el proceso, SMTPDebug=0 no despliega nada
        $mail->Host = 'smtp.live.com';
        $mail->Port = 587;
        /* 
          Usuario: contactocredenciales@bachilleres.edu.mx 
          Contrase�a: SiCredCB01
        */
        $mail->Username = 'contactocredenciales@bachilleres.edu.mx';
        $mail->Password = 'sicredCB01';
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;

        $email = 'contactocredenciales@bachilleres.edu.mx';
        $mail->setFrom($email,'Contacto Credenciales');
        $mail->addAddress('liliana.arroyo@bachilleres.edu.mx');
        $mail->addCC('alejandro.sanchezm@bachilleres.edu.mx');
        $mail->Subject = $asunto;
        $mail->CharSet = 'utf-8';
        $mail->msgHTML($cuerpo . "<br/><br/><b>" .$enviadoPor. "</b>");
        
        $mail->isHTML(true);

        if(!$mail->send()){
            return 'Error Enviar Mensaje';
        }else{
            return 'Mensaje Enviado';
        }

    }
    
    public function enviarCorreoR($asunto, $archivos, $tipo){
        $enviadoPor = "Contacto Credenciales - SiCred";
        $mensaje = "Archivos generados durante el proceso de carga de solicitudes de reposici&oacute;n a la BD. <br/><br/><b>" . $enviadoPor. "</b>";

        $mail = new PHPMailer (false);
        $mail->isSMTP();
        $mail->SMTPDebug = 0; //no muestra nada
        $mail->Host = 'smtp.live.com';
        $mail->Port = 587;
        $mail->Username = 'contactocredenciales@bachilleres.edu.mx';
        $mail->Password = 'sicredCB01';
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;

        $email = 'contactocredenciales@bachilleres.edu.mx';
        $mail->setFrom($email,'Contacto Credenciales - SiCred');
        
        $mail->addAddress('liliana.arroyo@bachilleres.edu.mx');
        /*
        $mail->addAddress('graciela_avig@bachilleres.edu.mx');
        $mail->addCC('liliana.arroyo@bachilleres.edu.mx'); */

        $mail->CharSet = 'utf-8';
        $mail->Subject = "=?ISO-8859-1?B?".base64_encode($asunto)."=?=";

        if ($tipo == "modificar"){
            $mensaje = $mensaje . "<br/><b>NOTA: </b>El archivo correo_alumnos_reposicion_modificar_.xlsx contiene algun correo con longitud mayor a 80 caracteres por lo que requiere de antenci&oacute;n para poder dar de alta la cuenta de correo.";
        }
        $mail->msgHTML($mensaje);
        $mail->isHTML(true);

        foreach ($archivos as $archivo){
            if (file_exists($archivo)){
                $mail->AddAttachment($archivo);
            }
        }

        if(($resp = $mail->send())== false){
            $msj = 'Error Enviar Mensaje';
        }else{
            $msj = 'Mensaje Enviado';
        }

        return $msj;

    }

}