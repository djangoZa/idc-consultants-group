<?php
class IDC_Tablet_Dropbox_Folder
{
	private $_path;
	private $_modified;

	public function __construct(Array $folder)
	{
		$this->_path = $folder['path'];
		$this->_modified = $folder['modified'];
	}

	public function isReadyToBeProcessed()
	{
		$out = false;

		$modifiedTimestamp = strtotime($this->_modified);
		$currentTimestamp = time();
		$differenceInSeconds = $currentTimestamp - $modifiedTimestamp;
		$tenMinutesinSeconds = 1;

		if($differenceInSeconds > $tenMinutesinSeconds)
		{
			$out = true;
		}
		
		return $out;
	}

	public function getPath()
	{
		return $this->_path;
	}

	public function getDataFilePath()
	{
		$out = $this->_path . '/data.txt';
		return $out;
	}
}