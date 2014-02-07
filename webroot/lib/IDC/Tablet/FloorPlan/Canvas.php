<?php
class IDC_Tablet_Floorplan_Canvas
{
	private $_dropboxService;
	private $_floorplans;
	private $_floorplanDimensions;

	public function __construct(IDC_Tablet_Dropbox_Service $dropboxService, Array $floorplans, $siteId)
	{
		$this->_siteId = $siteId;
		$this->_dropboxService = $dropboxService;
		$this->_floorplans = $floorplans;
		$this->_floorplanDimensions = $this->_getFloorplanDimensions();
	}

	public function run()
	{
		
	}

	private function _getFloorplanDimensions()
	{
		$out = array();
		$floorplanImageNames = $this->_getUniqueFloorplanNames();

		foreach($floorplanImageNames as $floorplanImageName)
		{
			$floorplanImageNameParts = explode('.', $floorplanImageName);
			$out[$floorplanImageName]['mobile'] = $this->_dropboxService->getFloorplanImageDimensions($this->_siteId, $floorplanImageNameParts[0] . '.' . $floorplanImageNameParts[1]);
			$out[$floorplanImageName]['large'] = $this->_dropboxService->getFloorplanImageDimensions($this->_siteId, $floorplanImageNameParts[0] . '_Large.' . $floorplanImageNameParts[1]);
		}

		return $out;
	}

	private function _getUniqueFloorplanNames()
	{
		$out = array();

		foreach ($this->_floorplans as $floorplan)
		{
			$out[] = $floorplan->getImageName();
		}

		$out = array_unique($out);

		return $out;
	}
}