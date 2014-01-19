<?php
class Marker
{
    private $_id;
    private $_section;
    private $_answers;

    public function __construct($options = array()) 
    {
    	$this->_id = $options->id;
    	$this->_section = $options->section;
    	$this->_answers = $options->answers;
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
    		$out[] = new Answer($answer);
    	}

    	return $out;
    }

    public function getSection()
    {
    	return $this->_section;
    }
}