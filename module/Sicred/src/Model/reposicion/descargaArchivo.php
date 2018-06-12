<?php
/**
 * Created by PhpStorm.
 * User: colbach
 * Date: 24/10/2017
 * Time: 11:28
 */

namespace Sicred\Model\reposicion;

class descargaArchivo{

    function descargaArch($fileUrl){
    	// OBTENEMOS EL TAMAÑO DEL ARCHIVO	
		if (substr($fileUrl,0,4)=='http') {
	        $fileSize = array_change_key_case(get_headers($fileUrl, 1),CASE_LOWER);
	        if ( strcasecmp($fileSize[0], 'HTTP/1.1 200 OK') != 0 ) { $fileSize = $fileSize['content-length'][1]; }
	        else { $fileSize = $fileSize['content-length']; }
	    } else { $fileSize = @filesize($fileUrl); }
	 
		// DESCARGAMOS EL ARCHIVO
		$ctype="application/octet-stream";
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false);
		header("Content-Type: $ctype");
	 
	 	//el nombre que se le asignara al archivo
		header("Content-Disposition: attachment; filename=\"".basename($fileUrl)."\";" );
		
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$fileSize);
		readfile("$fileUrl");
		
		//exit();

	}
 
   public function descargarZIP($rutas, $nombre){
        //Creamos un instancia de la clase ZipArchive
        $zip = new \ZipArchive();
        // Creamos y abrimos un archivo zip temporal
        $zip->open($nombre.".zip",ZipArchive::CREATE);
        // Añadimos un directorio
        $dir = "$nombre";
        $zip->addEmptyDir($dir);
        
        // nombres de los archivos
        $nom1 = basename($rutas[0]);
        $nom2 = basename($rutas[1]);

        //Añadimos un archivo dentro del directorio que hemos creado
        $zip->addFile(".$nom1.", $dir."/.$nom1.");
        $zip->addFile(".$nom2.", $dir."/.$nom2."); 

        // Una vez añadido los archivos deseados cerramos el zip.
        $zip->close();
        // Creamos las cabezeras que forzaran la descarga del archivo como archivo zip.
        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename=".$nombre.".zip");

        // leemos el archivo creado
        readfile($nombre.".zip");

        // Por último eliminamos el archivo temporal creado
        unlink($nombre.".zip");//Destruye el archivo temporal

    }
    
}