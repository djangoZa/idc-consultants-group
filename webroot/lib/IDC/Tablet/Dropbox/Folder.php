<?php
class IDC_Tablet_Dropbox_Folder
{
	private $_path;
	private $_modified;

	public function __construct(Array $folder)
	{
		$this->_path = $folder['path'];
	}

	public function isReadyToBeProcessed()
	{
		return true;
	}

	public function getPath()
	{
		return $this->_path;
	}

	public function getBaseName()
	{
		return basename($this->_path);
	}

	public function getDataFilePath()
	{
		$out = $this->_path . '/data.txt';
		return $out;
	}
}