<?php
class IDC_Tablet_Marker
{
    private $_id;
    private $_section;
    private $_answers;
    private $_x;
    private $_y;
    private $_markerIconsPath = '/vagrant/webroot/assets/img/marker_icons';
    private $_markerFontPath = '/vagrant/webroot/assets/fonts/interstate-black.ttf';

    public function __construct($options = array()) 
    {
    	$this->_id = $options->id;
    	$this->_section = $options->section;
    	$this->_answers = $options->answers;
        $this->_x = $options->x;
        $this->_y = $options->y;
    }

    public function getId()
    {
    	return $this->_id;
    }

    public function getAnswers()
    {
    	$out = array();

    	foreach ($this->_answers as $answer)
    	{
    		$out[] = new ID_Tablet_Answer($answer);
    	}

    	return $out;
    }

    public function getSection()
    {
    	return $this->_section;
    }

    public function getCoordinates()
    {
        return array(
            'x' => $this->_x, 
            'y' => $this->_y
        );
    }

    public function getIcon()
    {
        //create a marker canvas using the appropriate background
        $markerIconPath = '';
        switch($this->getSection())
        {
            case 'internal_circulation':
            case 'other':
            case 'warning':
            case 'auditorium':
            case 'controls_and_switches':
            case 'signage':
            case 'toilet':
            case 'ramp':
            case 'stairs':
            case 'elevator':
            case 'doors':
            case 'floor_surfaces':
            case 'internal_circulation':
            case 'external_circulation':
            case 'parking':
            case 'inactive':
                $markerIconPath = $this->_markerIconsPath . '/' . $this->getSection() . '.jpg';
                break;
            default:
                $markerIconPath = $this->_markerIconsPath . '/default.jpg';
                break;
        }

        $iconImage = imagecreatefromjpeg($markerIconPath);

        //Set the X and Y coordinates of the text
        $textXCoord = (imagesx($iconImage) / 2) - 12;
        $textYCoord = (imagesy($iconImage) / 2) + 10;

        //Print the section number to the image
        $colour = imagecolorallocate($iconImage, 255, 255, 255);
        $sectionNumber = $this->getId();
        imagettftext($iconImage, 20, 0, $textXCoord, $textYCoord, $colour, $this->_markerFontPath, $sectionNumber);

        return $iconImage;
    }
}