<?php
class IDC_Tablet_Output
{
	private $_dropboxService;
	private $_folder;
	private $_output;

	public function __construct(IDC_Tablet_Dropbox_Service $dropboxService)
	{
		$this->_dropboxService = $dropboxService;
	}

	public function setFolder(IDC_Tablet_Dropbox_Folder $folder)
	{
		$this->_folder = $folder;
		$this->_setOutput();
	}

	public function getSiteId()
	{
		$id = null;

		if (!empty($this->_output->floorplans))
		{
			$floorplan = Container::get('IDC_Tablet_FloorPlan', array(array_pop($this->_output->floorplans)));
			$name = $floorplan->getName();
			$parts = explode("_", $name);
			$id = $parts[0];
		}
		
		return $id;
	}

	private function _setOutput()
	{
		$json = $this->_dropboxService->getFileContents($this->_folder->getDataFilePath());
		$this->_output = json_decode($json);
	}
}