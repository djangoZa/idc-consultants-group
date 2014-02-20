<?php
class IDC_Tablet_Answer
{
    private $_photos;
    private $_feedback;
    private $_comment;

    public function __construct($options = array())
    {
    	$this->_photos = $options->photos;
    	$this->_feedback = $options->feedback;
    	$this->_comment = $options->comment;
        $this->_folder = $options->folder;
    }

    public function getPhotos()
    {
        $out = array();

        foreach($this->_photos as $photo)
        {
            $out[] = array(
                'name' => basename($photo),
                'folder' => $this->_folder
            );
        }

    	return $out;
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