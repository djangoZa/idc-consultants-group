<?php
error_reporting(E_ERROR);
ini_set('display_errors', 0);

require_once dirname(__FILE__) . "/../lib/bootstrap.php";

$options = getopt("s:");
$siteId = $options['s'];
$tabletDropboxService = Container::get('IDC_Tablet_Dropbox_Service');
$tabletFloorPlanService = Container::get('IDC_Tablet_FloorPlan_Service');

$dropboxSiteBasePath = '/vagrant/dropbox/idc-consultants-group/uploads/Processed/' . $siteId;

echo "OK: Start\n";

echo "OK: Fetching tablet outputs for site id ($siteId).\n";

$outputs = $tabletDropboxService->getOutputsBySiteId($siteId);

echo "OK: Fetching floorplans from outputs.\n";

$floorplans = $tabletFloorPlanService->getFloorPlansFromOutputs($outputs);

echo "OK: Building array of rows for Excel.\n";

foreach ($floorplans as $floorPlan)
{
	$markers = $floorPlan->getMarkers();
	foreach($markers as $marker)
	{
        $answers = $marker->getAnswers();

        foreach($answers as $answer)
        {
	        $rows[] = array(
	        	'Floor_Plan' => $floorPlan->getName(),
	        	'#' => $marker->getId(),
	        	'Section' => $marker->getSection(),
	        	'Onsite_Images' => $answer->getPhotos(),
	        	'SANS_Compliance' => $answer->getFeedback(),
	        	'Comments' => $answer->getComment(),
                'Units' => $answer->getUnits(),
	        	'SANS_Recommendations' => '',
	        	'Optional_Recommendations' => '',
	        	'QS_SANS_Costing' => '',
				'QS_Optional_Costing' => ''
	        );	
        }
	}
}

echo "OK: Building Excel file.\n";

// define the columns
$columns = range("A","Z");

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setTitle("Archibus");

// Add some data
$objPHPExcel->setActiveSheetIndex(0);
$images = array();

echo "OK: Formatting rows in Excel file.\n";

//Add the rows
foreach ($rows as $rowId => $row)
{
    foreach($row as $columnId => $value)
    {
    	$columnIndex = array_search($columnId, array_keys($row));
    	$cellIndex = $columns[$columnIndex] . ($rowId + 2);

        //Set the zebra stripes on alternate cells
        if($rowId % 2)
        {
            $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $objPHPExcel->getActiveSheet()->getStyle($cellIndex)->getFill()->applyFromArray(
                array(
                    'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array('rgb' => 'E9E9E9'),
                )
            );
        }

    	switch($columnId)
    	{
    		case '#':
    			$value = sprintf("%02s", $value);
    			break;
    	    case 'Onsite_Images':

    	        //set the initial offset
    	        $offset = 0;

                foreach($value as $key => $image)
                {
                	try{
                		//get the image
                		$imageFilePath = $image['name'];
                		$tmpImageFilePath = $dropboxSiteBasePath . '/Data/' . $image['folder'] . '/' . $imageFilePath;

                		if (file_exists($tmpImageFilePath))
                		{
							//embed the image
							$objDrawing = new PHPExcel_Worksheet_Drawing();
		                    $objDrawing->setPath($tmpImageFilePath);
		                    $objDrawing->setCoordinates($cellIndex);
		                    $objDrawing->setResizeProportional(true);
		                    $objDrawing->setWidth(200);
		                    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

                            //set the image offset
                            $size = getimagesize($tmpImageFilePath);
                            $height = $objDrawing->getHeight();
                            $offset = ($key == 0)? 0 : $offset + $height + 2;
                            $objDrawing->setOffsetY(($key > 0) ? $offset : 0);

                            $images[$cellIndex][] = array(
                                'height' => $height,
                                'offset' => $offset,
                                'path' => $tmpImageFilePath
                            );

                            unset($objDrawing);
                		}
                	} catch (Exception $e) {
                		//do something if image is not found
                	}
                }

    	        break;
    		case 'Section':
    		    $value = ucfirst(str_replace("_"," ",$value));
    		    break;
    	}

    	//write value
    	$objPHPExcel->getActiveSheet()->SetCellValue($cellIndex, $value);
    }
}

//Add the headings and format cells
$columnNames = array_keys(array_pop($rows));

$objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3);
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0);
$objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0);
$objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0);
$objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0);

foreach($columnNames as $columnId => $columnName)
{
    $columnLetter = $columns[$columnId];
    $cellIndex =  $columnLetter . '1';
    $objPHPExcel->getActiveSheet()->SetCellValue($cellIndex, str_replace('_', ' ', $columnName));
    $objPHPExcel->getActiveSheet()->getStyle($cellIndex)->getFont()->setBold(true);

    switch ($columnName)
    {
        case 'SANS_Recommendations':
        case 'Optional_Recommendations':
        case 'QS_SANS_Costing':
        case 'QS_Optional_Costing':
        case 'Comments':
        case 'SANS_Compliance':
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(20);
            $objPHPExcel->getActiveSheet()->getStyle($cellIndex . ':' . $columnLetter . $objPHPExcel->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true); 
            break;
        case 'Onsite_Images':
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(25);
            //size the height based on the images
            foreach ($rows as $rowId => $row)
            {
                $height = 0;
                $cellIndex = $columnLetter . $rowId;

                if(isset($images[$cellIndex]))
                {
                    $cellImages = $images[$cellIndex];

                    foreach ($cellImages as $cellImage)
                    {
                        $height += $cellImage['height'];
                    }
                    
                    if ($height > 0)
                    {
                        $objPHPExcel->getActiveSheet()->getRowDimension($rowId)->setRowHeight($height);
                    }     
                }
            }

            break;
        case 'Section':
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth(17);
            break;
        default;
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setAutoSize(true);
            break;
    }

}

echo "OK: Writing Excel file.\n";

try {
	
	// Save Excel 2007 file
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	$objWriter->save($dropboxSiteBasePath . '/index.xlsx');

} catch (Exception $e) {
	error_log("Could not write the XLSX file to dropbox", 0);
}