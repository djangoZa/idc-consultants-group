<?php
set_include_path(
	get_include_path() . 
	PATH_SEPARATOR . dirname(__FILE__) . 
	PATH_SEPARATOR . dirname(__FILE__) . '/Dropbox'
);

require_once dirname(__FILE__) . '/Dropbox/autoload.php';

//entities
require_once(dirname(__FILE__) . '/IDC/Tablet/Answer.php');
require_once(dirname(__FILE__) . '/IDC/Tablet/FloorPlan.php');
require_once(dirname(__FILE__) . '/IDC/Tablet/Marker.php');
require_once(dirname(__FILE__) . '/IDC/Tablet/Output.php');
require_once(dirname(__FILE__) . '/IDC/Tablet/Dropbox/Folder.php');
require_once(dirname(__FILE__) . '/IDC/Tablet/Floorplan/Canvas.php');

//services
require_once(dirname(__FILE__) . '/IDC/Tablet/Dropbox/Service.php');
require_once(dirname(__FILE__) . '/IDC/Tablet/Floorplan/Service.php');

//container
require_once dirname(__FILE__) . '/Container.php';