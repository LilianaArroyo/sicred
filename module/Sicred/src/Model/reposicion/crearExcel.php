<?php
/**
 * Created by PhpStorm.
 * User: colbach
 * Date: 10/01/2018
 * Time: 11:26
 */

namespace Sicred\Model\reposicion;

use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Driver;
use phpoffice\phpexcel\Classes\PHPExcel\IOFactory;

use Sicred\Model;

class crearExcel
{
    /** excel que contiene la tabla de intervalos de impresion **/
    public function excelResultado($resultado, $fecha, $cont){
        $objPHPExcel = new \PHPExcel();
        // Leemos un archivo Excel 2007
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $archivo = $_SERVER['DOCUMENT_ROOT'].'/ArchivosExcel/Resultado/plantilla.xlsx';
        $objPHPExcel = $objReader->load($archivo);
        // Indicamos que se pare en la hoja uno del libro
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet()->SetCellValue('B1', $fecha);

        $i = 3;
        foreach ($resultado as $dato) {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, $dato[0]);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, $dato[1]);
            if ($dato[2] != $dato[3])
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, $dato[2]."-".$dato[3]);
            else
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, $dato[2]);

            $i++;
        }

        for($j=3; $j<$i;$j++){
            $objPHPExcel->getActiveSheet()->getStyle('A'.$j.':C'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$j.':C'.$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, $cont);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $ruta = $_SERVER['DOCUMENT_ROOT'].'/ArchivosExcel/Resultado/';
        $nom_archi_sal = $ruta."REGISTROS_".str_replace("/", "", $fecha).".xlsx";
        $objWriter->save($nom_archi_sal);

        return $nom_archi_sal;

    }

    public function excelCorreos($correos,$fecha, $tipo){
        $objPHPExcel = new \PHPExcel();
        // Leemos un archivo Excel 2007
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $archivo = $_SERVER['DOCUMENT_ROOT'].'/ArchivosExcel/Correos/plantilla.xlsx';
        $objPHPExcel = $objReader->load($archivo);
        // Indicamos que se pare en la hoja uno del libro
        $objPHPExcel->setActiveSheetIndex(0);

        $i = 2;
        foreach ($correos as $dato) {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, $dato[0]);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, $dato[1]);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, $dato[2]);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$i, $dato[3]);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$i, $dato[4]);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$i, $dato[5]);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$i, $dato[6]);
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$i, $dato[7]);
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$i, $dato[8]);
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$i, $dato[9]);
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$i, $dato[10]);
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$i, $dato[11]);
            $objPHPExcel->getActiveSheet()->SetCellValue('M'.$i, "CENTRO DE ESTUDIOS NO. ".$dato[12]);
            $objPHPExcel->getActiveSheet()->SetCellValue('N'.$i, $dato[13]);
            $objPHPExcel->getActiveSheet()->SetCellValue('O'.$i, $dato[14]);

            $i++;
        }

        for($j=2; $j<$i;$j++){
            $objPHPExcel->getActiveSheet()->getStyle('A'.$j.':O'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$j.':O'.$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $ruta = $_SERVER['DOCUMENT_ROOT'].'/ArchivosExcel/Correos/';
        
        $nom_archi_sal = $ruta."correo_alumnos_reposicion_".$tipo."_".$fecha.".xlsx";
        $objWriter->save($nom_archi_sal);
        return $nom_archi_sal;
    }
    
    public function crearCSV($correos, $fecha, $tipo){
        //$conn = new Model\Connect();
        //$db = $conn->connBD();

        $ruta = $_SERVER['DOCUMENT_ROOT'].'/ArchivosExcel/Correos/';
        $archivo_csv = $ruta."correo_alumnos_reposicion_".$tipo."_".$fecha.".csv";
        //chmod($archivo_csv, 0777);
        

        $csv = fopen($archivo_csv, 'x+');
        
        if($csv){
            fputcsv($csv, array('UserPrincipalName', 'AlternateEmailAddresses', 'FirstName', 'LastName', 'Title', 'Office', 'Fax', 'MobilePhone', 'PhoneNumber', 'City', 'Country', 'Password', 'Department', 'StreetAddress', 'PostalCode'));
            
            foreach ($correos as $dato) {
                $dato[12] = "CENTRO DE ESTUDIOS NO. ".$dato[12];
    
                fputs($csv, implode($dato, ',').PHP_EOL);
            }
            
            fclose($csv);
            
            return $archivo_csv;
              
        }else{
          return "";
        }
       
    }
    
    public function matriculaError($datos, $fecha, $tipo){
        $objPHPExcel = new \PHPExcel();
        // Leemos un archivo Excel 2007
        $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        $archivo = $_SERVER['DOCUMENT_ROOT'].'/ArchivosExcel/Resultado/plantillaerror.xlsx';
        $objPHPExcel = $objReader->load($archivo);
        // Indicamos que se pare en la hoja uno del libro
        $objPHPExcel->setActiveSheetIndex(0);

        $i = 2;
        foreach ($datos as $dato) {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, $dato[0]);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$i, $dato[1]." ".$dato[2]." ".$dato[3]);
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$i, $dato[4]);

            $i++;
        }

        for($j=2; $j<$i;$j++){
            $objPHPExcel->getActiveSheet()->getStyle('A'.$j.':C'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$j.':C'.$j)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $ruta = $_SERVER['DOCUMENT_ROOT'].'/ArchivosExcel/Resultado/';
        $nom_archi_sal = $ruta.$tipo.str_replace("/", "", $fecha).".xlsx";
        $objWriter->save($nom_archi_sal);
        
        return $nom_archi_sal;

    }

}