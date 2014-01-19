<?php
require_once('/vagrant/webroot/lib/bootstrap.php');

$basePath = '/vagrant/webroot/uploads/tablet1/';
$jsonText = file_get_contents($basePath . '/data.txt');
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

include 'PHPExcel.php';

/** PHPExcel_Writer_Excel2007 */
include 'PHPExcel/Writer/Excel2007.php';

// define the columns
$columns = range("A","Z");

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setTitle("Archibus");

// Add some data
$objPHPExcel->setActiveSheetIndex(0);

//Add the headings
$columnNames = array_keys(array_pop($rows));
foreach($columnNames as $columnId => $columnName)
{
    $objPHPExcel->getActiveSheet()->SetCellValue($columns[$columnId] . '1', ucfirst($columnName));
}

//Add the rows
foreach ($rows as $rowId => $row)
{
    foreach($row as $columnId => $value)
    {
    	$columnIndex = array_search($columnId, array_keys($row));
    	$cellIndex = $columns[$columnIndex] . ($rowId + 2);
    	switch($columnId)
    	{
    		case 'pointerReference':
    			$value = (String) str_pad($value, 3, '0', STR_PAD_LEFT);
    			break;
    	    case 'compliance':
    	    	//$objPHPExcel->getActiveSheet()->getStyle($cellIndex)->getAlignment()->setWrapText(true);
    	    	break;
    	    case 'images':
                foreach($value as $image)
                {
                    $objDrawing = new PHPExcel_Worksheet_Drawing();
                    $objDrawing->setPath($basePath . '/' . basename($image));
                    $objDrawing->setCoordinates($cellIndex);
                    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
                }    
    	        break;
    		case 'section':
    		    $value = ucfirst(str_replace("_"," ",$value));
    		    break;
    	}

    	//write value
    	$objPHPExcel->getActiveSheet()->SetCellValue($cellIndex, $value);
    }
}

// Rename sheet
$objPHPExcel->getActiveSheet()->setTitle('Simple');
		
// Save Excel 2007 file
$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
$objWriter->save($basePath . '/spreadsheet.xlsx');    

echo "done";