<?php
class IDC_Tablet_Floorplan_Service
{
	public function getFloorPlansFromOutputs(Array $outputs)
	{
		$out = array();

		foreach ($outputs as $output)
		{
			$floorplans = $output->getFloorplans();

			foreach ($floorplans as $floorplan)
			{
				$out[] = $floorplan;
			}

		}

		return $out;
	}
}