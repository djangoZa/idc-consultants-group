<?php
class IDC_Tablet_Floorplan_Service
{
	public function getFloorPlansFromOutputs(Array $outputs)
	{
		$out = array();
		$markerId = 0;

		foreach ($outputs as $output)
		{
			$floorplans = $output->getFloorplans();

			foreach ($floorplans as $floorplan)
			{
				//ensure that each markers id will run sequentially
				$tmpMarkers = array();
				$markers = $floorplan->getMarkers();
				if (!empty($markers))
				{
					foreach($markers as $key => $marker)
					{
						$markerId += 1;
						$marker->setId($markerId);
						$tmpMarkers[] = $marker;
					}

					//set the tmpmarkers to the floorplan
					$floorplan->setMarkers($tmpMarkers);
				}

				//Append to the output array
				$out[] = $floorplan;
			}

		}

		return $out;
	}

	public function generateAndSaveOverlayedFloorplans(IDC_Tablet_Floorplan_Canvas $canvas)
	{
		$canvas->setFloorplanImages();
		$canvas->pinMarkersToFloorplans();
		$canvas->saveFloorplansToDropbox();
	}
}