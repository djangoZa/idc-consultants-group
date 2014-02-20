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
    private $_folder;

    public function __construct($options = array()) 
    {
    	$this->_section = $options->section;
    	$this->_answers = $options->answers;
        $this->_x = $options->x;
        $this->_y = $options->y;
        $this->_folder = $options->folder;
    }

    public function setId($value)
    {
        $this->_id = $value;
    }

    public function getId()
    {
    	return sprintf("%02s", $this->_id);
    }

    public function getAnswers()
    {
    	$out = array();

    	foreach ($this->_answers as $answer)
    	{
            $answer->folder = $this->_folder;
    		$out[] = new IDC_Tablet_Answer($answer);
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
                $markerIconPath = $this->_markerIconsPath . '/' . $this->getSection() . '.png';
                break;
            default:
                $markerIconPath = $this->_markerIconsPath . '/default.png';
                break;
        }

        $iconImage = imagecreatefrompng($markerIconPath);

        //Print the section number to the image
        $colour = imagecolorallocate($iconImage, 255, 255, 255);
        $sectionNumber = $this->getId();

        //Calculate the texts coordinates to be centre
        $font = 5;
        $fontWidth = ImageFontWidth($font);
        $fontHeight = ImageFontHeight($font);

        $textWidth = $fontWidth * strlen($sectionNumber);
        $textHeight = $fontHeight;

        $textXCoord = ceil((56 - $textWidth) / 2);
        $textYCoord = ceil((56 - $textHeight) / 2);

        ImageString($iconImage, $font, $textXCoord, $textYCoord, $sectionNumber, $colour);

        return $iconImage;
    }
}