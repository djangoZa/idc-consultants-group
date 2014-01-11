<?php
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