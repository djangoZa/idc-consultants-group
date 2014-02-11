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
			$this->_floorplanImages[$floorplan->getName()]['image'] = $floorplan->getImage('_L');
			$this->_floorplanImages[$floorplan->getName()]['siteId'] = $floorplan->getSiteId();
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
				$backgroundImage = $this->_floorplanImages[$floorplan->getName()]['image'];
				$markerIcon = $marker->getIcon();
				$markerCoordinates = $marker->getCoordinates();
				imagecopy(
					$backgroundImage,
					$markerIcon,
					($markerCoordinates['x'] * 1.99) - ($markerIconWidth / 2),
					($markerCoordinates['y'] * 1.99) - ($markerIconHeight / 2),
					0,
					0,
					$markerIconWidth,
					$markerIconHeight
				);
			}
		}

		
	}

	public function saveFloorplansToDropbox()
	{
		foreach($this->_floorplanImages as $version => $values)
		{
			//write the floorplan to the tmp directory
			$tmpFloorplanImagePath = "/tmp/idc-consultants-group/" . $version . '.jpg';
			imagejpeg($values['image'], $tmpFloorplanImagePath);

			//upload the floorplan to dropbox
			$this->_dropboxService->uploadFloorplanImage($tmpFloorplanImagePath, $values['siteId']);
		}
	}
}