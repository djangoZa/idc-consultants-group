<?php
require_once "../lib/bootstrap.php";
include 'PHPExcel.php';
include 'PHPExcel/Writer/Excel2007.php';

$accessToken = "hhEvuMaZfGEAAAAAAAAAAbdMwClYVaAQ9NVk57Xn134SdsWsawf3eHHyvGkUC405";
$dbxClient = new Dropbox\Client($accessToken, "PHP-Example/1.0");
$basePath = '/idc-consultants-group/uploads';
$pendingPath = $basePath . "/Pending";

//poll dropbox for changes
$cursor = null;
while(true)
{
	$folderMetadata = $dbxClient->getMetadataWithChildren($pendingPath);
	$tablets = $folderMetadata['contents'];

	foreach($tablets as $tablet)
	{
		$folderMetadata = $dbxClient->getMetadataWithChildren($tablet['path']);
		$outputs = $folderMetadata['contents'];

		foreach($outputs as $output)
		{
			//move contents to processing folder
			$folderName = basename($output['path']) . "_" . uniqid();
			$processingFolder = $basePath . "/Processing/" . $folderName;
			$dbxClient->move($output['path'], $processingFolder);

			//process contents moved to processing folder
			////get the data.txt file
			try{
				$tmpDataFilePath = '/tmp/idc-consultants-group/data-' . uniqid();
				$dataFilePath = $processingFolder . '/data.txt';
				$fileHandle = fopen($tmpDataFilePath . '.txt', "w+b");
				$fileMetadata = $dbxClient->getFile($dataFilePath, $fileHandle);
				fclose($fileHandle);
			} catch (Exception $e) {
				echo $e->getMessage();
				//HANDLE THIS EXCEPTION
			}

			////build the spreadsheet
			$jsonText = file_get_contents($tmpDataFilePath . '.txt');
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
			                	try{

			                		//get the image
			                		$imageFileName = basename($image);
			                		$tmpImageFilePath = '/tmp/idc-consultants-group/' . uniqid() . '-' . $imageFileName;
									$imageFilePath = $processingFolder . '/' . $imageFileName;
									$fileHandle = fopen($tmpImageFilePath, "w+b");
									$fileMetadata = $dbxClient->getFile($imageFilePath, $fileHandle);
									fclose($fileHandle);

									//embed the image
									$objDrawing = new PHPExcel_Worksheet_Drawing();
				                    $objDrawing->setPath($tmpImageFilePath);
				                    $objDrawing->setCoordinates($cellIndex);
				                    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

			                	} catch (Exception $e) 
			                	{
			                		echo $e->getMessage();
			                	}
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
			$objWriter->save($tmpDataFilePath . '.xlsx');

			//upload generated xls file to processing folder
			$f = fopen($tmpDataFilePath . '.xlsx', "rb");
			$result = $dbxClient->uploadFile($processingFolder . "/data.xlsx", Dropbox\WriteMode::add(), $f);
			fclose($f);

			//move processed folder to done
			$pendingFolder = $basePath . "/Done/" . date('d M Y') . "/" . $folderName;
			$dbxClient->move($processingFolder, $pendingFolder);
		}
	}

	//wait for 5 minutes
	sleep(30);
}