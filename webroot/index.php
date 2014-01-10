<?php
class FloorPlan
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

class Answer
{
    private $_photos;
    private $_feedback;
    private $_comment;

    public function __construct($options = array())
    {
    	$this->_photos = $options->photos;
    	$this->_feedback = $options->feedback;
    	$this->_comment = $options->comment;
    }

    public function getPhotos()
    {
    	return $this->_photos;
    }

    public function getFeedback()
    {
    	return $this->_feedback;
    }

    public function getComment()
    {
    	return $this->_comment;
    }
}

$jsonText = file_get_contents(dirname(__FILE__) . '/uploads/data.txt');
$json = json_decode($jsonText);
$rows = array();

foreach ($json->floorplans as $floorPlan)
{
	$floorPlan = new FloorPlan($floorPlan);

	$markers = $floorPlan->getMarkers();
	foreach($markers as $marker)
	{

        $answers = $marker->getAnswers();
        foreach($answers as $answer)
        {
	        $rows[] = array(
	        	'floor' => $floorPlan->getName(),
	        	'pointerReference' => $marker->getId(),
	        	'section' => $marker->getSection(),
	        	'compliance' => $answer->getFeedback(),
	        	'images' => $answer->getPhotos(),
	        	'recommendations' => '',
	        	'comments' => $answer->getComment(),
	        	'qsCosting' => ''
	        );	
        }
	}
}

print(json_encode($rows));