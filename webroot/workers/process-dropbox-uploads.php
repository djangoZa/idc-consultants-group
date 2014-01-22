<?php
ini_set('error_reporting', E_ERROR);

require_once dirname(__FILE__) . "/../lib/bootstrap.php";
include 'PHPExcel.php';
include 'PHPExcel/Writer/Excel2007.php';

$accessToken = "hhEvuMaZfGEAAAAAAAAAAbdMwClYVaAQ9NVk57Xn134SdsWsawf3eHHyvGkUC405";
$dbxClient = new Dropbox\Client($accessToken, "PHP-Example/1.0");
$basePath = '/idc-consultants-group/uploads';
$pendingPath = $basePath . "/Pending";

//poll dropbox for changes
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
			//get some date information
			$tenMinutes = 600;
			$modifiedTime = strtotime($output['modified']);
			$currentTime = time();

			//if the folder was modified more than 10 minutes ago, continue
			if(($currentTime - $modifiedTime) > $tenMinutes)
			{
				try {

					//move contents to processing folder
					$folderName = basename($output['path']) . "_" . uniqid();
					$processingFolder = $basePath . "/Processing/" . $folderName;
					$dbxClient->move($output['path'], $processingFolder);

					try {

						//process contents moved to processing folder
						//write the contents of the dropbox data.txt file to the local temp directory
						$tmpDataFilePath = '/tmp/idc-consultants-group/data-' . uniqid();
						$dataFilePath = $processingFolder . '/data.txt';
						$fileHandle = fopen($tmpDataFilePath . '.txt', "w+b");
						$fileMetadata = $dbxClient->getFile($dataFilePath, $fileHandle);
						fclose($fileHandle);

						try {

							//get the data dump in order to build the spreadsheet
							$jsonText = file_get_contents($tmpDataFilePath . '.txt');
							$json = json_decode($jsonText);

							//build the spreadsheet if we have floorplans
							if (isset($json->floorplans))
							{
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
									        	'Floor_Plan' => $floorPlan->getName(),
									        	'Pointer_Reference' => $marker->getId(),
									        	'Section' => $marker->getSection(),
									        	'Onsite_Images' => $answer->getPhotos(),
									        	'SANS_Compliance' => $answer->getFeedback(),
									        	'Comments' => $answer->getComment(),
									        	'SANS_Recommendations' => '',
									        	'Optional_Recommendations' => '',
									        	'QS_SANS_Costing' => '',
												'QS_Optional_Costing' => ''
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
									$cellIndex = $columns[$columnId] . '1';
								    $objPHPExcel->getActiveSheet()->SetCellValue($cellIndex, str_replace('_', ' ', $columnName));
								    $objPHPExcel->getActiveSheet()->getStyle($cellIndex)->getFont()->setBold(true);
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
								    		case 'Pointer_Reference':
								    			$value = (String) str_pad($value, 3, '0', STR_PAD_LEFT);
								    			break;
								    	    case 'Onsite_Images':

								    	        //set the initil offset
								    	        $offset = 0;

								                foreach($value as $key => $image)
								                {
								                	try{

								                		//get the image
								                		$imageFileName = basename($image);
								                		$tmpImageFilePath = '/tmp/idc-consultants-group/' . uniqid() . '-' . $imageFileName;
														$imageFilePath = $processingFolder . '/' . $imageFileName;
														$fileHandle = fopen($tmpImageFilePath, "w+b");
														$fileMetadata = $dbxClient->getFile($imageFilePath, $fileHandle);
														fclose($fileHandle);

														//set the image offset
														$size = getimagesize($tmpImageFilePath);
														$height = $size[1];
														$offset = ($key == 0)? 0 : $offset + $height + 2;

														//embed the image
														$objDrawing = new PHPExcel_Worksheet_Drawing();
									                    $objDrawing->setPath($tmpImageFilePath);
									                    $objDrawing->setCoordinates($cellIndex);
									                    $objDrawing->setOffsetY(($key > 0) ? $offset : 0);
									                    $objDrawing->setResizeProportional(true);
									                    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());

								                	} catch (Exception $e) 
								                	{
								                		echo $e->getMessage();
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
							    
							    try {
									
							    	// Save Excel 2007 file
									$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
									$objWriter->save($tmpDataFilePath . '.xlsx');

									//upload generated xls file to processing folder
									$f = fopen($tmpDataFilePath . '.xlsx', "rb");
									$result = $dbxClient->uploadFile($processingFolder . "/data.xlsx", Dropbox\WriteMode::add(), $f);
									fclose($f);

									try {

			  							//move processed folder to done
										$doneFolder = $basePath . "/Done/" . date('d M Y') . "/" . $folderName;
										$dbxClient->move($processingFolder, $doneFolder);

									} catch (Exception $e) {
										error_log("Could not move processed payload to the done directory", 0);
									}
							    } catch (Exception $e) {
			 					    error_log("Could not write the XLSX file to dropbox", 0);
							    }
							} else {
								error_log("Could not get any floorplans from the json data dump", 0);
							}
						} catch (Exception $e) {
							error_log("Could not build the spreadsheet", 0);
						}
					} catch (Exception $e) {
						error_log("Could not write json data dump to the local temp directory", 0);
					}
				} catch (Exception $e) {
					error_log("Could not move payload to the processing directory", 0);
				}
			} else {
				error_log("Cant process folder because its younger than 10 minutes",0);
			}
		}
	}

	//wait for 5 minutes or 300 seconds
	sleep(300);
}