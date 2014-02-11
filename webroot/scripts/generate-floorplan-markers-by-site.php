<?php
error_reporting(E_ERROR);
ini_set('display_errors', 0);

require_once dirname(__FILE__) . "/../lib/bootstrap.php";

$options = getopt("s:");
$siteId = $options['s'];
$tabletDropboxService = Container::get('IDC_Tablet_Dropbox_Service');
$tabletFloorPlanService = Container::get('IDC_Tablet_FloorPlan_Service');

echo "OK: Start\n";

echo "OK: Fetching tablet outputs for site id ($siteId). This may take a few minutes...\n";

$outputs = $tabletDropboxService->getOutputsBySiteId($siteId);
file_put_contents("/tmp/outputs", serialize($outputs));
//$outputs = unserialize(file_get_contents("/tmp/outputs"));

if (!empty($outputs))
{
	echo "OK: Got tablet outputs.\n";

	echo "OK: Fetching floorplan and marker data from outputs. This may take a few minutes...\n";
	
	//Fetch the all the floorplans across all data files
	$floorplans = $tabletFloorPlanService->getFloorPlansFromOutputs($outputs);
    file_put_contents("/tmp/floorplans", serialize($floorplans));
	//$floorplans = unserialize(file_get_contents("/tmp/floorplans"));
	echo "OK: Got floorplan and marker data.\n";

	//Create the floorplan skeleton
	$tabletDropboxService->createFloorplanFolderSkeleton($siteId);
	echo "OK: Created skeleton folder structure for floorplans.\n";

	//Verify all floorplan images have been uploaded
	echo "OK: Verifying that all required floorplan images have been uploaded.\n";
	$hasAllRequiredFloorplanImages = $tabletDropboxService->hasAllRequiredFloorplanImages($siteId, $floorplans);
	//$hasAllRequiredFloorplanImages = array('result' => true);

	if ($hasAllRequiredFloorplanImages['result'] == true)
	{
		echo "OK: All floorplan images have been uploaded.\n";

		//Construct the floorplan canvas
		$tabletFloorPlanCanvas = Container::get('IDC_Tablet_FloorPlan_Canvas', array($tabletDropboxService, $floorplans, $siteId));
		echo "OK: Flooplan canvas constructed.\n";

		//Generate a JPG representation of the floorplan canvas
		$tabletFloorPlanService->generateAndSaveOverlayedFloorplans($tabletFloorPlanCanvas);
		echo "OK: Generated floorplan images with overlayed markers\n";

	} else {

		foreach ($hasAllRequiredFloorplanImages['messages'] as $message)
		{
			echo "NOTICE: $message\n";
		}
		
		echo "NOTICE: Please upload to Dropbox and re-run this script.\n";

	}

} else {

	echo "ERROR: Cant find any uploaded data\n";

}

echo "OK: End\n\n";