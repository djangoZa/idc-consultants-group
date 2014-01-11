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

print(json_encode($rows));