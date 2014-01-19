<?php
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . PATH_SEPARATOR . dirname(__FILE__) . '/Dropbox');

require_once dirname(__FILE__) . '/Tablet/FloorPlan.php';
require_once dirname(__FILE__) . '/Tablet/Marker.php';
require_once dirname(__FILE__) . '/Tablet/Answer.php';

require_once dirname(__FILE__) . '/Dropbox/autoload.php';