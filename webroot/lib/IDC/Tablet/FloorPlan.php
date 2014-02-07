<?php
class IDC_Tablet_FloorPlan
{
	private $_name;
    private $_imageName;
    private $_markers;
    
    public function __construct($options = array())
    {
    	$this->_name = $options->name;
        $this->_imageName = basename($options->path);
    	$this->_markers = $options->markers;
        
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
            $out[] = new IDC_Tablet_Marker($marker);
    	}

    	return $out;
    }
}