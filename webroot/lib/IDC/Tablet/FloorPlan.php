<?php
class IDC_Tablet_FloorPlan
{
	private $_name;
    private $_siteId;
    private $_imageName;
    private $_imagePaths = array();
    private $_markers;
    
    public function __construct($options = array())
    {
    	$this->_name = $options->name;
        $this->_imageName = basename($options->path);
    	$this->_markers = $options->markers;
    }

    public function setMarkers(Array $markers)
    {
        $this->_markers = $markers;
    }

    public function getSiteId()
    {
        $name = $this->getName();
        $parts = explode("-", $name);
        $id = $parts[0];
        return $id;
    }

    public function getName()
    {
    	return $this->_name;
    }

    public function getImageName()
    {
        return $this->_imageName;
    }

    public function getMarkers()
    {
    	$out = array();

    	foreach($this->_markers as $marker)
    	{
            if($marker instanceof IDC_Tablet_Marker)
            {
                $out[] = $marker;
            } else {
                $out[] = new IDC_Tablet_Marker($marker);    
            }
            
    	}

    	return $out;
    }

    public function getImage($key = '')
    {
        $out = array();

        $imagePath = $this->_imagePaths[$key];
        $image = imagecreatefromjpeg($imagePath);
        $out = $image;

        return $out;
    }

    public function setImagePaths(Array $imagePaths)
    {
        $this->_imagePaths = $imagePaths;
    }
}