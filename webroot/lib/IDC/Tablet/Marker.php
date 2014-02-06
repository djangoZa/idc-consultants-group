<?php
class IDC_Tablet_Marker
{
    private $_id;
    private $_section;
    private $_answers;
    private $_x;
    private $_y;

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
}