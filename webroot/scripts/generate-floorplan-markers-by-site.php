<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__FILE__) . "/../lib/bootstrap.php";

$options = getopt("s:");
$siteId = $options['s'];
$tabletDropboxService = Container::get('IDC_Tablet_Dropbox_Service');
$tabletFloorPlanService = Container::get('IDC_Tablet_FloorPlan_Service');

echo "OK: Start\n";

echo "OK: Fetching tablet outputs for site id ($siteId). This may take a few minutes...\n";

$outputs = $tabletDropboxService->getOutputsBySiteId($siteId);
$coordinatesPerFloor = array();

if (!empty($outputs))
{
	echo "OK: Got tablet outputs for site id ($siteId)\n";

	$floorplans = $tabletFloorPlanService->getFloorPlansFromOutputs($outputs);

	echo "OK: Got floorplans for site id ($siteId)\n";

	//Get all the coordinates per floor
	foreach ($floorplans as $floorplan)
	{

		$markers = $floorplan->getMarkers();

		if(!empty($markers))
		{
			$coordinates = array();

			foreach ($markers as $marker)
			{
				$coordinatesPerFloor[$floorplan->getName()][] = $marker->getCoordinates();
			}
		}

	}

	//Generate a floorplan canvas
	if (!empty($coordinatesPerFloor))
	{
		echo "OK: Found coordinates for site id ($siteId)\n";

		foreach ($coordinatesPerFloor as $floor => $coordinates)
		{
			//Instantiate the floorplan canvas
			$tabletFloorPlanCanvas = Container::get('IDC_Tablet_FloorPlan_Canvas', array($tabletDropboxService));
			$tabletFloorPlanCanvas->setFloor($floor);
			$tabletFloorPlanCanvas->setCoordinates($coordinates);

			//Generate an image from the floorplan canvas
			var_dump($tabletFloorPlanCanvas);
		}

	} else {

		echo "NOTICE: Did not find any coordinates for site id ($siteId)\n";

	}

} else {

	echo "ERROR: Cant find data for site id ($siteId)\n";

}

echo "OK: End\n";