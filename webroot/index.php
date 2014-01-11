<?php
require_once(dirname(__FILE__) . '/lib/bootstrap.php');

$jsonText = file_get_contents(dirname(__FILE__) . '/uploads/data.txt');
$json = json_decode($jsonText);
$rows = array();

foreach ($json->floorplans as $floorPlan)
{
	$floorPlan = new FloorPlan($floorPlan);

	$markers = $floorPlan->getMarkers();
	foreach($markers as $marker)
	{

        $answers = $marker->getAnswers();
        foreach($answers as $answer)
        {
	        $rows[] = array(
	        	'floor' => $floorPlan->getName(),
	        	'pointerReference' => $marker->getId(),
	        	'section' => $marker->getSection(),
	        	'compliance' => $answer->getFeedback(),
	        	'images' => $answer->getPhotos(),
	        	'recommendations' => '',
	        	'comments' => $answer->getComment(),
	        	'qsCosting' => ''
	        );	
        }
	}
}

var_dump($rows);

include 'PHPExcel.php';

/** PHPExcel_Writer_Excel2007 */
include 'PHPExcel/Writer/Excel2007.php';

// Create new PHPExcel object
echo date('H:i:s') . " Create new PHPExcel object\n";
$objPHPExcel = new PHPExcel();

// Set properties
echo date('H:i:s') . " Set properties\n";
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
$objPHPExcel->getProperties()->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.");

// Add some data
echo date('H:i:s') . " Add some data\n";
$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Hello');
$objPHPExcel->getActiveSheet()->SetCellValue('B2', 'world!');
$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Hello');
$objPHPExcel->getActiveSheet()->SetCellValue('D2', 'world!');

// Rename sheet
echo date('H:i:s') . " Rename sheet\n";
$objPHPExcel->getActiveSheet()->setTitle('Simple');
		
// Save Excel 2007 file
echo date('H:i:s') . " Write to Excel2007 format\n";
$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save('/tmp/spreadsheet.xlsx');

// Echo done
echo date('H:i:s') . " Done writing file.\r\n";