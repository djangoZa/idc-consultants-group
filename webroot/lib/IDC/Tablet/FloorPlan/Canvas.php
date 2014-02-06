<?php
class IDC_Tablet_Floorplan_Canvas
{
	private $_dropboxService;
	private $_floor;
	private $_coordinates;

	public function __construct(IDC_Tablet_Dropbox_Service $dropboxService)
	{
		$this->_dropboxService = $dropboxService;
	}

	public function setFloor($floor)
	{
		$this->_floor = $floor;
	}

	public function setCoordinates(Array $coordinates)
	{
		$this->_coordinates = $coordinates;
	}
}