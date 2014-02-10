<?php
class IDC_Tablet_Floorplan_Canvas
{
	private $_dropboxService;
	private $_floorplans;
	private $_floorplanImages;

	public function __construct(IDC_Tablet_Dropbox_Service $dropboxService, Array $floorplans)
	{
		$this->_dropboxService = $dropboxService;
		$this->_floorplans = $floorplans;
	}

	public function setFloorplanImages()
	{
		foreach($this->_floorplans as $floorplan)
		{
			$this->_floorplanImages[$floorplan->getName()] = $floorplan->getImage('_Large');
		}
	}

	public function pinMarkersToFloorplans()
	{
		foreach($this->_floorplans as $floorplan)
		{
			$markers = $floorplan->getMarkers();
			$markerIconWidth = 56;
			$markerIconHeight = 56;
			foreach($markers as $marker)
			{
				$backgroundImage = $this->_floorplanImages[$floorplan->getName()];
				$markerIcon = $marker->getIcon();
				$markerCoordinates = $marker->getCoordinates();
				imagecopy(
					$backgroundImage,
					$markerIcon,
					$markerCoordinates['x'] - ($markerIconWidth / 2),
					$markerCoordinates['y'] - ($markerIconWidth / 2),
					0,
					0,
					$markerIconWidth,
					$markerIconHeight
				);
			}
		}

		foreach($this->_floorplanImages as $version => $image)
		{
			imagejpeg($image, "/vagrant/floorplan-" . $version);
		}
	}
}