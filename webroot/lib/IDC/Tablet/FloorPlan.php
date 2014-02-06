<?php
class IDC_Tablet_FloorPlan
{
	private $_name;
    private $_markers;

    public function __construct($options = array())
    {
    	$this->_name = $options->name;
    	$this->_markers = $options->markers;
    }

    public function getName()
    {
    	return $this->_name;
    }

    public function getMarkers()
    {
    	$out = array();

    	foreach($this->_markers as $marker)
    	{
            $out[] = new Marker($marker);
    	}

    	return $out;
    }
}